#!/usr/bin/env bash
#
# Prépare une archive prête à téléverser sur un hébergement mutualisé.
#
# Un mutualisé ne dispose ni de Node ni, souvent, de Composer : tout est
# donc construit ICI, sur votre machine, et l'archive contient déjà le
# dossier vendor/ et les assets compilés.
#
#   ./deploy/cpanel/build.sh
#
# Produit, dans deploy/dist/ :
#   app/          les deux dossiers, prêts à glisser dans un client FTP
#   public_html/
#   electroniques-AAAAMMJJ-HHMM.zip   la même chose compressée, pour le
#                                     gestionnaire de fichiers de cPanel
#
set -euo pipefail

cd "$(dirname "$0")/../.."
RACINE="$(pwd)"
HORODATAGE="$(date +%Y%m%d-%H%M)"
SORTIE="$RACINE/deploy/dist"
# On construit directement dans deploy/dist : les dossiers restent
# disponibles après coup, pour téléverser sans passer par l'archive.
TRAVAIL="$SORTIE"
ARCHIVE="$SORTIE/electroniques-$HORODATAGE.zip"

# « composer install --no-dev » ci-dessous retire PHPUnit et Pint de votre
# vendor/ : sans restauration, vous ne pourriez plus lancer les tests après
# une construction. On remet donc l'environnement de développement en
# partant, y compris en cas d'interruption.
nettoyer() {
    if [ "${DEV_A_RESTAURER:-0}" = "1" ]; then
        echo "→ Restauration des dépendances de développement"
        composer install --quiet --no-interaction
    fi
}
trap nettoyer EXIT

echo "→ Vérification des prérequis"
for outil in composer npm zip; do
    command -v "$outil" >/dev/null 2>&1 || { echo "   ERREUR : « $outil » est introuvable." >&2; exit 1; }
done

echo "→ Dépendances PHP (sans les outils de développement)"
DEV_A_RESTAURER=1
composer install --no-dev --optimize-autoloader --classmap-authoritative --no-interaction --quiet

echo "→ Compilation des assets"
npm ci --silent
npm run build

echo "→ Nettoyage de la construction précédente"
rm -rf "$TRAVAIL/app" "$TRAVAIL/public_html"
mkdir -p "$TRAVAIL/app"

echo "→ Copie des fichiers de l'application"
# --delete-excluded n'a pas lieu d'être : on part d'un dossier vide.
rsync -a \
    --exclude='.git' \
    --exclude='.github' \
    --exclude='node_modules' \
    --exclude='tests' \
    --exclude='deploy/dist' \
    --exclude='.env' \
    --exclude='.env.*' \
    `# Inutiles sur un mutualisé : ni Docker, ni Node, ni exécution de tests.` \
    --exclude='Dockerfile' \
    --exclude='docker-compose.yml' \
    --exclude='docker' \
    --exclude='.dockerignore' \
    --exclude='phpunit.xml' \
    --exclude='package.json' \
    --exclude='package-lock.json' \
    --exclude='vite.config.js' \
    --exclude='tailwind.config.js' \
    --exclude='postcss.config.js' \
    --exclude='bin' \
    --exclude='resources/js' \
    --exclude='resources/css' \
    --exclude='storage/logs/*' \
    --exclude='storage/framework/cache/data/*' \
    --exclude='storage/framework/sessions/*' \
    --exclude='storage/framework/views/*' \
    --exclude='database/*.sqlite' \
    --exclude='public/storage' \
    --exclude='.phpunit.result.cache' \
    "$RACINE/" "$TRAVAIL/app/"

echo "→ Séparation racine web / application"
mkdir -p "$TRAVAIL/public_html"
# Le contenu de public/ part dans public_html, le reste demeure hors du web.
cp -r "$TRAVAIL/app/public/." "$TRAVAIL/public_html/"
rm -rf "$TRAVAIL/app/public"
# Point d'entrée adapté à cette disposition.
cp "$RACINE/deploy/cpanel/index.php" "$TRAVAIL/public_html/index.php"

echo "→ Dossiers inscriptibles"
mkdir -p \
    "$TRAVAIL/app/storage/app/public" \
    "$TRAVAIL/app/storage/framework/cache/data" \
    "$TRAVAIL/app/storage/framework/sessions" \
    "$TRAVAIL/app/storage/framework/views" \
    "$TRAVAIL/app/storage/logs" \
    "$TRAVAIL/app/bootstrap/cache"

# Aucune configuration mise en cache dans l'archive : elle figerait les
# valeurs de CETTE machine. Les caches se construisent sur le serveur,
# une fois le .env en place.
rm -f "$TRAVAIL/app/bootstrap/cache/"*.php

echo "→ Modèle de configuration"
cp "$RACINE/deploy/cpanel/.env.cpanel.example" "$TRAVAIL/app/.env.example.cpanel"

echo "→ Compression"
rm -f "$SORTIE"/electroniques-*.zip
(cd "$TRAVAIL" && zip -qr "$ARCHIVE" app public_html)

TAILLE_ZIP="$(du -h "$ARCHIVE" | cut -f1)"
TAILLE_APP="$(du -sh "$TRAVAIL/app" | cut -f1)"
TAILLE_WEB="$(du -sh "$TRAVAIL/public_html" | cut -f1)"

cat <<MESSAGE

   Prêt dans deploy/dist/

     app/          $TAILLE_APP   ->  /home/COMPTE/app        (HORS racine web)
     public_html/  $TAILLE_WEB   ->  racine web du domaine

     electroniques-$HORODATAGE.zip  ($TAILLE_ZIP)
                   les deux dossiers compressés, si vous passez par le
                   gestionnaire de fichiers de cPanel plutôt que par FTP.

   Rappels : le .env va dans app/ sur le serveur (modèle fourni :
   app/.env.example.cpanel), et sans la tâche cron du planificateur,
   aucun email ne partira. Procédure : section « cPanel » du README.

MESSAGE
