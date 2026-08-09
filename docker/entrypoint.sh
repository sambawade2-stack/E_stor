#!/bin/sh
set -e

# ---------------------------------------------------------------------------
# Préparation du conteneur avant de céder la main au processus principal.
#
# Tout ce qui dépend de l'environnement est fait ICI et non à la construction
# de l'image : mettre en cache la configuration au moment du build figerait
# les valeurs de ce moment-là, et l'application déployée utiliserait une base
# ou une URL erronées.
# ---------------------------------------------------------------------------

artisan() {
    su-exec www-data php /var/www/html/artisan "$@"
}

# --- Garde-fous -------------------------------------------------------------

# Une APP_KEY absente OU malformée produit exactement le même symptôme :
# toutes les pages renvoient 500 (« Unsupported cipher or incorrect key
# length »), alors que /up répond et que les journaux semblent normaux au
# premier coup d'œil. On vérifie donc aussi la longueur décodée, pour
# échouer ici avec un message clair plutôt qu'à chaque requête.
php -r '
    $key = (string) getenv("APP_KEY");

    if ($key === "") {
        fwrite(STDERR, "ERREUR : APP_KEY est vide.\n");
        exit(1);
    }

    $raw = str_starts_with($key, "base64:")
        ? base64_decode(substr($key, 7), true)
        : $key;

    if ($raw === false || ! in_array(strlen($raw), [16, 32], true)) {
        fwrite(STDERR, sprintf(
            "ERREUR : APP_KEY invalide (%d octets une fois décodée, 32 attendus).\n",
            $raw === false ? 0 : strlen($raw),
        ));
        exit(1);
    }
' || {
    echo "         Générez-la avec « php artisan key:generate --show »" >&2
    echo "         puis renseignez-la dans les variables d'environnement." >&2
    exit 1
}

# --- Contrôle de la configuration mail --------------------------------------
# Un envoi mal configuré ne se voit pas depuis le site : les notifications
# sont asynchrones, elles échouent dans la file et les commandes continuent
# de passer. On le signale donc bruyamment au démarrage.

if [ "${MAIL_MAILER:-smtp}" = "smtp" ] && [ -z "${MAIL_HOST:-}" ]; then
    echo "ATTENTION : MAIL_MAILER=smtp mais MAIL_HOST est vide." >&2
    echo "            Aucun email ne partira (confirmation de commande," >&2
    echo "            alerte administrateur, vérification d'adresse)." >&2
fi

case "${MAIL_FROM_ADDRESS:-}" in
    ''|no-reply@localhost|hello@example.com)
        echo "ATTENTION : MAIL_FROM_ADDRESS n'est pas configurée." >&2
        echo "            Renseignez une adresse de votre domaine, sinon les" >&2
        echo "            messages seront rejetés par les serveurs destinataires." >&2
        ;;
esac

# --- Attente de la base de données -----------------------------------------
# Au premier démarrage, MySQL met quelques secondes à accepter les
# connexions. Sans cette attente, la migration échoue et le conteneur
# redémarre en boucle.

if [ "${DB_CONNECTION:-mysql}" = "mysql" ]; then
    echo "→ Attente de la base ${DB_HOST:-mysql}:${DB_PORT:-3306}…"
    php /usr/local/bin/wait-for-db.php
    echo "→ Base disponible."
fi

# --- Stockage public --------------------------------------------------------
# public/storage doit pointer vers storage/app/public, monté sur un volume.
# Le lien vit dans l'image, la cible dans le volume : il faut donc le
# (re)créer à chaque démarrage.

if [ ! -L /var/www/html/public/storage ]; then
    echo "→ Création du lien public/storage."
    artisan storage:link --force
fi

# --- Migrations -------------------------------------------------------------
# Le worker partage cette image : il ne doit pas migrer en parallèle de
# l'application. RUN_MIGRATIONS=false le désactive de son côté.

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    echo "→ Migrations."
    artisan migrate --force --no-interaction
fi

# --- Caches d'exécution -----------------------------------------------------

echo "→ Mise en cache de la configuration, des routes et des vues."
artisan config:cache
artisan route:cache
artisan view:cache
artisan event:cache

echo "→ Prêt."

exec "$@"
