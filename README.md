# Électroniques Stores

Boutique e-commerce d'accessoires électroniques — Laravel 12, Blade, Tailwind CSS, Alpine.js, MySQL.

> *La technologie à portée de main* : smartphones, écouteurs Bluetooth, chargeurs, power banks, répéteurs WiFi et accessoires.

## Fonctionnalités

- **Vitrine** : accueil complet (hero, catégories, vedettes, promos, avis, newsletter), catalogue avec filtres/tri/recherche, fiches produit (galerie, zoom, caractéristiques, avis, produits similaires), pages statiques
- **Panier & checkout** : panier en session, coupons, estimation de livraison par zone, commande invité en une étape
- **Comptes clients** : historique des commandes, rattachement automatique des commandes invité par email
- **Paiements** : architecture multi-passerelles (PayDunya → Wave, Orange Money, cartes ; paiement à la livraison), webhooks confirmés serveur-à-serveur
- **Administration** : dashboard (CA, ventes mensuelles, top produits, stock faible), CRUD produits/catégories/marques/coupons, gestion des commandes avec facture imprimable, clients, modération des avis, paramètres, journal d'activité
- **Notifications** : emails en file d'attente (confirmation client, alerte admin, changements de statut)
- **SEO & sécurité** : sitemap XML, robots.txt, canonical, OpenGraph, JSON-LD, en-têtes de sécurité, CSRF, rôles/permissions, journalisation

## Installation (développement)

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed     # SQLite par défaut ; crée l'admin + données de démo
php artisan storage:link
npm run build                  # ou npm run dev
composer run serve             # démarre avec des limites d'upload relevées (10 Mo/image)
```

Admin : l'adresse `SHOP_ADMIN_EMAIL` (`.env`). Si `ADMIN_DEFAULT_PASSWORD` est laissé vide,
le seeder génère un mot de passe aléatoire et l'affiche **une seule fois** dans la console —
notez-le à ce moment-là.

> ⚠️ N'utilisez pas `php artisan serve` seul pour uploader des images (photos produits, logos…) :
> la limite `upload_max_filesize` par défaut de PHP (souvent 2 Mo) est ignorée par le serveur
> intégré et bloque silencieusement l'upload. `composer run serve` délègue à [`bin/serve`](bin/serve),
> qui relève cette limite. En production (Apache/Nginx + PHP-FPM), `public/.user.ini` s'applique
> automatiquement.
>
> `bin/serve` est un script `sh` (Linux/macOS). Il traduit aussi les arrêts volontaires du serveur
> — Ctrl+C (130) ou SIGTERM (143) — en code de sortie 0 : sans ça, Composer signalait chaque arrêt
> comme une erreur (« returned with error code 143 »). Les vraies erreurs remontent normalement.

### Passer sur MySQL

```sql
CREATE DATABASE electroniques_stores CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Dans `.env` : `DB_CONNECTION=mysql` + identifiants, puis `php artisan migrate:fresh --seed`.

## Mise en production — checklist

1. `.env` : `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://…`
2. **Laisser `ADMIN_DEFAULT_PASSWORD` vide** : le seeder génère alors un mot de passe aléatoire affiché une seule fois. Ne jamais committer de mot de passe dans `.env.example`
3. MySQL configuré (voir ci-dessus) — les données de démonstration ne sont pas seedées en production
4. SMTP réel (`MAIL_*`) pour les emails de commande **et de vérification d'adresse** — sans SMTP fonctionnel, les clients ne peuvent pas vérifier leur email, et les commandes passées en invité ne leur sont jamais rattachées
5. Worker de queue : `php artisan queue:work` (superviser avec Supervisor/systemd)
6. Clés PayDunya (`PAYDUNYA_*`, `PAYDUNYA_MODE=live`) — le paiement en ligne s'active automatiquement dès qu'elles sont renseignées ; déclarer l'IPN `https://votre-domaine/webhooks/paydunya`
7. Optimisations : `php artisan config:cache route:cache view:cache event:cache` et `npm run build`
8. HTTPS obligatoire (forcé automatiquement en production) + certificat — le cookie de session passe en `Secure` automatiquement dès `APP_ENV=production`
9. Sauvegardes régulières : base de données + `storage/app/public` (images)
10. Vérifier `https://votre-domaine/sitemap.xml` et soumettre à Google Search Console

## Tests

```bash
php artisan test   # 61 tests, 208 assertions
```

## Architecture

- `app/Services/Cart` — panier en session
- `app/Services/Checkout` — création de commande transactionnelle (verrou stock)
- `app/Services/Payments` — passerelles de paiement (interface `PaymentGateway`)
- `app/Services/Images` — optimisation WebP des images (Intervention Image)
- `app/Enums` — statuts de commande/paiement avec transitions autorisées
- `app/Events` + `app/Listeners` + `app/Notifications` — emails de commande
