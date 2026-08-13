#!/usr/bin/env bash
#
# Installation / mise à jour sur hébergement mutualisé.
#
# À lancer DEPUIS LE SERVEUR (Terminal cPanel ou SSH), dans le dossier de
# l'application :
#
#     cd ~/app
#     bash deploy/cpanel/install.sh
#
# Sans danger à relancer : le script détecte ce qui est déjà fait. Utilisez-le
# aussi bien pour la première mise en ligne que pour les mises à jour.
#
set -uo pipefail

cd "$(dirname "$0")/../.."

PHP="${PHP_BIN:-php}"
ERREURS=0

titre() { printf '\n\033[1m%s\033[0m\n' "$1"; }
ok()    { printf '  \033[32m✓\033[0m %s\n' "$1"; }
avert() { printf '  \033[33m!\033[0m %s\n' "$1"; }
echec() { printf '  \033[31m✗\033[0m %s\n' "$1"; ERREURS=$((ERREURS + 1)); }

# ---------------------------------------------------------------------------
titre "1. Vérifications"

if [ ! -f artisan ]; then
    echec "Fichier « artisan » introuvable."
    echo "     Placez-vous dans le dossier de l'application : cd ~/app"
    exit 1
fi
ok "Dossier de l'application reconnu"

# Le PHP par défaut d'un mutualisé est souvent une vieille version, alors
# qu'une 8.2+ est disponible ailleurs. Mieux vaut le dire tout de suite que
# de laisser échouer une commande sur une erreur de syntaxe incompréhensible.
VERSION_PHP="$($PHP -r 'echo PHP_VERSION;' 2>/dev/null)"
if [ -z "$VERSION_PHP" ]; then
    echec "PHP introuvable (commande « $PHP »)."
    echo "     Indiquez le chemin complet, par exemple :"
    echo "     PHP_BIN=/opt/cpanel/ea-php83/root/usr/bin/php bash deploy/cpanel/install.sh"
    exit 1
fi

if $PHP -r 'exit(PHP_VERSION_ID < 80200 ? 1 : 0);' 2>/dev/null; then
    ok "PHP $VERSION_PHP"
else
    echec "PHP $VERSION_PHP — la version 8.2 minimum est requise."
    echo "     Changez la version dans cPanel (« Sélectionner la version de PHP »),"
    echo "     ou indiquez un autre binaire via PHP_BIN=… (voir ci-dessus)."
    exit 1
fi

if [ ! -f .env ]; then
    echec "Fichier « .env » absent."
    echo "     Copiez le modèle puis renseignez-le :"
    echo "     cp .env.example.cpanel .env"
    exit 1
fi
ok "Fichier .env présent"

if ! grep -qE '^APP_KEY=base64:.+' .env; then
    echec "APP_KEY absente ou incomplète dans .env."
    echo "     Sans elle, toutes les pages renverront une erreur 500."
    exit 1
fi
ok "APP_KEY renseignée"

# ---------------------------------------------------------------------------
titre "2. Connexion à la base de données"

# On vide les caches d'abord : un config.php hérité d'une installation
# précédente ferait lire d'anciens identifiants et le diagnostic serait faux.
$PHP artisan config:clear >/dev/null 2>&1

if $PHP artisan migrate:status >/dev/null 2>&1; then
    ok "Connexion établie"
else
    echec "Connexion impossible."
    echo "     Vérifiez DB_DATABASE, DB_USERNAME et DB_PASSWORD dans .env."
    echo "     Rappel : cPanel préfixe les noms avec votre compte, par exemple"
    echo "     « moncompte_boutique » et non « boutique »."
    exit 1
fi

# ---------------------------------------------------------------------------
titre "3. Tables"

if $PHP artisan migrate --force --no-interaction >/dev/null 2>&1; then
    ok "Migrations appliquées"
else
    echec "Échec des migrations."
    $PHP artisan migrate --force --no-interaction 2>&1 | tail -15
    exit 1
fi

# ---------------------------------------------------------------------------
titre "4. Rôles et compte administrateur"

$PHP artisan db:seed --class=RoleAndPermissionSeeder --force --no-interaction >/dev/null 2>&1 \
    && ok "Rôles et permissions en place" \
    || echec "Échec de la création des rôles"

# Le seeder n'est joué qu'en l'absence d'administrateur : le relancer
# réinitialiserait un mot de passe déjà changé.
NB_ADMINS="$($PHP artisan tinker --execute='echo \App\Models\User::role("admin")->count();' 2>/dev/null | tr -dc '0-9')"

if [ "${NB_ADMINS:-0}" = "0" ]; then
    echo ""
    echo "  ┌─────────────────────────────────────────────────────────┐"
    echo "  │  MOT DE PASSE ADMINISTRATEUR — affiché UNE SEULE FOIS    │"
    echo "  └─────────────────────────────────────────────────────────┘"
    $PHP artisan db:seed --class=AdminUserSeeder --force --no-interaction 2>&1 | grep -vE '^\s*$'
    echo ""
else
    ok "Compte administrateur déjà créé (mot de passe inchangé)"
fi

# ---------------------------------------------------------------------------
titre "5. Accès aux photos"

# public/ ayant été déplacé dans la racine web, le lien doit y être créé et
# non dans l'application. On le pose donc à la main : « storage:link »
# viserait un dossier public/ qui n'existe plus ici.
RACINE_WEB=""
for candidat in ../public_html ../www ../htdocs; do
    [ -d "$candidat" ] && { RACINE_WEB="$candidat"; break; }
done

if [ -z "$RACINE_WEB" ]; then
    avert "Racine web introuvable à côté de l'application."
    echo "     Si votre domaine pointe directement sur app/public, lancez :"
    echo "     $PHP artisan storage:link"
elif [ -L "$RACINE_WEB/storage" ]; then
    ok "Lien vers les photos déjà en place"
elif ln -s "$(pwd)/storage/app/public" "$RACINE_WEB/storage" 2>/dev/null; then
    ok "Lien vers les photos créé dans $(basename "$RACINE_WEB")"
else
    avert "Création du lien impossible (symlink() désactivé par l'hébergeur)."
    echo "     Sans ce lien, AUCUNE photo produit ne s'affichera."
    echo "     Créez-le depuis le gestionnaire de fichiers : dans la racine web,"
    echo "     un raccourci « storage » vers app/storage/app/public."
fi

chmod -R ug+rwX storage bootstrap/cache 2>/dev/null \
    && ok "Droits d'écriture sur storage/" \
    || avert "Droits sur storage/ non modifiables — à vérifier si une erreur survient"

# ---------------------------------------------------------------------------
titre "6. Optimisation"

for commande in config:cache route:cache view:cache event:cache; do
    $PHP artisan "$commande" >/dev/null 2>&1 \
        && ok "$commande" \
        || echec "$commande"
done

# ---------------------------------------------------------------------------
if [ "$ERREURS" -gt 0 ]; then
    titre "Terminé avec $ERREURS erreur(s)"
    exit 1
fi

titre "Installation terminée"

URL="$(grep -E '^APP_URL=' .env | cut -d= -f2- | tr -d '"' | sed 's:/*$::')"

cat <<MESSAGE

  Votre site : $URL
  Administration : $URL/login

  Il reste DEUX choses à faire dans cPanel :

  1. La tâche cron — sans elle, aucun email ne partira jamais
     (ni confirmation au client, ni alerte de commande) :

     * * * * * $(command -v "$PHP" || echo "$PHP") $(pwd)/artisan schedule:run >> /dev/null 2>&1

  2. Le certificat SSL (AutoSSL) : le site force le https, et le
     paiement en ligne l'exige.

  Après chaque mise à jour des fichiers, relancez simplement ce script.

MESSAGE
