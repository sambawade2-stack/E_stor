#!/usr/bin/env bash
#
# Déploiement automatique depuis GitHub.
#
# Lancé par cron toutes les 5 minutes, il regarde si le dépôt distant a
# avancé. Si rien n'a changé, il s'arrête aussitôt sans rien faire ni rien
# écrire — l'immense majorité des exécutions.
#
# Installation (une seule fois), dans cPanel > Tâches Cron :
#
#     */5 * * * * /bin/bash /home/COMPTE/app/deploy/cpanel/auto-deploy.sh
#
# Journal : storage/logs/deploy.log
#
# Pourquoi un cron plutôt qu'un webhook GitHub : un mutualisé n'expose pas
# de point d'entrée de déploiement, et ouvrir une URL capable de déclencher
# des commandes exigerait de la sécuriser sérieusement. Le cron ne demande
# aucun secret et fonctionne même dépôt privé, avec les identifiants déjà
# enregistrés par le clone.
#
set -uo pipefail

cd "$(dirname "$0")/../.." || exit 1
RACINE="$(pwd)"

PHP="${PHP_BIN:-php}"
BRANCHE="${DEPLOY_BRANCH:-main}"
WEB="${WEB_ROOT:-$HOME/public_html}"
JOURNAL="$RACINE/storage/logs/deploy.log"
VERROU="$RACINE/storage/framework/deploy.lock"

mkdir -p "$(dirname "$JOURNAL")"

noter() { printf '[%s] %s\n' "$(date '+%Y-%m-%d %H:%M:%S')" "$1" >> "$JOURNAL"; }

# --- Verrou -----------------------------------------------------------------
# Un déploiement peut dépasser cinq minutes (composer install). Sans verrou,
# le cron suivant démarrerait par-dessus et deux « migrate » se
# chevaucheraient.
if [ -e "$VERROU" ]; then
    # Verrou de plus de 30 min : reliquat d'une exécution interrompue.
    if [ -n "$(find "$VERROU" -mmin +30 2>/dev/null)" ]; then
        noter "Verrou périmé ignoré."
        rm -f "$VERROU"
    else
        exit 0
    fi
fi

# --- Y a-t-il du nouveau ? --------------------------------------------------
# Silencieux : ce cas représente presque toutes les exécutions, il ne doit
# rien écrire dans le journal sous peine de le rendre illisible.
git fetch origin "$BRANCHE" --quiet 2>/dev/null || exit 0

LOCAL="$(git rev-parse HEAD 2>/dev/null)"
DISTANT="$(git rev-parse "origin/$BRANCHE" 2>/dev/null)"

[ -z "$DISTANT" ] && exit 0
[ "$LOCAL" = "$DISTANT" ] && exit 0

# --- Déploiement ------------------------------------------------------------
touch "$VERROU"

# La maintenance est levée quoi qu'il arrive : une sortie prématurée
# laisserait sinon le site affichant « 503 » indéfiniment.
terminer() {
    $PHP artisan up >/dev/null 2>&1
    rm -f "$VERROU"
}
trap terminer EXIT

noter "──────────────────────────────────────────────"
noter "Nouveaux commits détectés :"
git log --oneline "$LOCAL..$DISTANT" 2>/dev/null | head -20 | while read -r ligne; do
    noter "    $ligne"
done

# composer.lock a-t-il bougé ? Réinstaller les dépendances à chaque
# déploiement coûterait une minute pour rien la plupart du temps.
DEPS_CHANGEES=0
git diff --name-only "$LOCAL" "$DISTANT" 2>/dev/null | grep -q '^composer\.lock$' && DEPS_CHANGEES=1

$PHP artisan down --render="errors::503" >/dev/null 2>&1

# reset --hard plutôt que pull : sur une cible de déploiement, l'état doit
# être exactement celui du dépôt. Les fichiers non suivis (.env, photos
# envoyées) ne sont pas touchés.
if ! git reset --hard "origin/$BRANCHE" --quiet 2>>"$JOURNAL"; then
    noter "ÉCHEC : impossible de se positionner sur origin/$BRANCHE."
    exit 1
fi
noter "Code mis à jour → $(git rev-parse --short HEAD)"

if [ "$DEPS_CHANGEES" = "1" ]; then
    noter "composer.lock modifié : réinstallation des dépendances"
    if command -v composer >/dev/null 2>&1; then
        composer install --no-dev --optimize-autoloader --no-interaction --quiet 2>>"$JOURNAL" \
            && noter "Dépendances à jour" \
            || noter "ÉCHEC de composer install"
    else
        noter "ATTENTION : Composer introuvable, vendor/ n'a pas été mis à jour."
    fi
fi

# --- Partie publique --------------------------------------------------------
# Copie sans effacement : « storage » (lien vers les photos) doit survivre.
if [ -d "$WEB" ]; then
    cp -R "$RACINE/public/." "$WEB/" 2>>"$JOURNAL"
    cp "$RACINE/deploy/cpanel/index.php" "$WEB/index.php" 2>>"$JOURNAL"
    noter "Racine web synchronisée"

    if [ ! -e "$WEB/storage" ]; then
        ln -s "$RACINE/storage/app/public" "$WEB/storage" 2>/dev/null \
            && noter "Lien vers les photos recréé"
    fi
else
    noter "ATTENTION : racine web introuvable ($WEB) — définissez WEB_ROOT."
fi

# --- Base et caches ---------------------------------------------------------
ANOMALIES=0

if $PHP artisan migrate --force --no-interaction >/dev/null 2>>"$JOURNAL"; then
    noter "Migrations appliquées"
else
    noter "ÉCHEC des migrations"
    ANOMALIES=$((ANOMALIES + 1))
fi

for commande in config:cache route:cache view:cache event:cache; do
    $PHP artisan "$commande" >/dev/null 2>&1 || ANOMALIES=$((ANOMALIES + 1))
done
noter "Caches régénérés"

# Le bilan doit dire la vérité : annoncer « terminé » après une migration
# ratée laisserait croire le site à jour alors qu'il tourne peut-être sur un
# schéma de base incomplet.
if [ "$ANOMALIES" -gt 0 ]; then
    noter "TERMINÉ AVEC $ANOMALIES ERREUR(S) — le site tourne, mais vérifiez ci-dessus."
else
    noter "Déploiement réussi."
fi
