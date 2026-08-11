<?php

/*
|--------------------------------------------------------------------------
| Point d'entrée pour hébergement mutualisé (cPanel) — SOLUTION DE REPLI
|--------------------------------------------------------------------------
|
| À n'utiliser QUE si votre hébergeur refuse de faire pointer la racine du
| domaine vers le dossier « public » de l'application. Demandez-le d'abord :
| dans cPanel, « Domaines » permet souvent de modifier la racine, et c'est
| de loin la configuration la plus propre.
|
| Pourquoi cela compte : si la racine web est le dossier de l'application,
| alors « .env », les journaux et la base SQLite deviennent téléchargeables
| depuis un navigateur. Vos identifiants de base et vos clés de paiement
| seraient exposés.
|
| Disposition attendue avec ce fichier :
|
|     /home/COMPTE/
|         app/                  <- l'application, HORS de la racine web
|             bootstrap/ vendor/ storage/ .env …
|         public_html/          <- racine web du domaine
|             index.php         <- CE fichier
|             .htaccess         <- copié depuis public/.htaccess
|             build/ storage/ favicon.ico … (contenu de public/)
|
| Ajustez APP_BASE_PATH ci-dessous si vous nommez le dossier autrement.
|
*/

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
| Chemin vers le dossier de l'application, relatif à ce fichier.
| « /../app » signifie : un niveau au-dessus de public_html, dossier « app ».
*/
$basePath = __DIR__.'/../app';

if (! is_file($basePath.'/vendor/autoload.php')) {
    http_response_code(500);

    exit(
        'Application introuvable. Vérifiez le chemin $basePath dans index.php : '
        .'il doit désigner le dossier contenant vendor/ et bootstrap/.'
    );
}

// Mode maintenance (php artisan down)
if (file_exists($maintenance = $basePath.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $basePath.'/vendor/autoload.php';

/** @var Application $app */
$app = require_once $basePath.'/bootstrap/app.php';

/*
| Laravel déduit ses chemins depuis l'emplacement de bootstrap/app.php, qui
| est correct ici. En revanche « public_path() » doit désigner CE dossier —
| celui réellement servi par le web — et non « app/public », sinon les liens
| vers les images et les assets compilés pointeraient à côté.
*/
$app->usePublicPath(__DIR__);

$app->handleRequest(Request::capture());
