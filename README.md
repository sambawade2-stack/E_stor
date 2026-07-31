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
php artisan serve
```

Admin : `admin@electroniques-stores.com` / mot de passe défini par `ADMIN_DEFAULT_PASSWORD` (`.env`).

### Passer sur MySQL

```sql
CREATE DATABASE electroniques_stores CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Dans `.env` : `DB_CONNECTION=mysql` + identifiants, puis `php artisan migrate:fresh --seed`.

## Mise en production — checklist

1. `.env` : `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://…`
2. **Changer `ADMIN_DEFAULT_PASSWORD`** avant le premier seed (ou changer le mot de passe admin ensuite)
3. MySQL configuré (voir ci-dessus) — les données de démonstration ne sont pas seedées en production
4. SMTP réel (`MAIL_*`) pour les emails de commande
5. Worker de queue : `php artisan queue:work` (superviser avec Supervisor/systemd)
6. Clés PayDunya (`PAYDUNYA_*`, `PAYDUNYA_MODE=live`) — le paiement en ligne s'active automatiquement dès qu'elles sont renseignées ; déclarer l'IPN `https://votre-domaine/webhooks/paydunya`
7. Optimisations : `php artisan config:cache route:cache view:cache event:cache` et `npm run build`
8. HTTPS obligatoire (forcé automatiquement en production) + certificat
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
