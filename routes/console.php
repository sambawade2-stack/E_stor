<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Tâches planifiées
|--------------------------------------------------------------------------
|
| Un hébergement mutualisé (cPanel) n'autorise aucun processus permanent :
| impossible d'y laisser tourner « queue:work ». Or les notifications de
| commande sont asynchrones (ShouldQueue) — sans traitement, AUCUN email
| ne part : ni la confirmation au client, ni l'alerte au gérant. Les jobs
| s'empilent en base, silencieusement.
|
| On vide donc la file à chaque minute, depuis le planificateur, qui ne
| demande qu'une seule tâche cron sur le serveur :
|
|     * * * * * /usr/local/bin/php /home/COMPTE/app/artisan schedule:run >> /dev/null 2>&1
|
| Sur un serveur dédié ou en conteneur, préférez un worker permanent
| (Supervisor, ou le service « worker » du docker-compose) : le délai
| tombe alors à zéro. Cette planification reste inoffensive dans ce cas,
| mais devient inutile — commentez-la si vous disposez d'un worker.
|
*/

Schedule::command('queue:work', [
    // S'arrête dès la file vidée : sur mutualisé, un processus qui
    // s'éternise finit tué par l'hébergeur, souvent au milieu d'un job.
    '--stop-when-empty',
    // Trois essais avant de classer le job en échec : un serveur SMTP
    // momentanément indisponible ne doit pas perdre une notification.
    '--tries=3',
    '--backoff=10',
    // Garde-fou : jamais plus de 50 s, pour que deux exécutions
    // consécutives ne se chevauchent pas.
    '--max-time=50',
])
    ->everyMinute()
    // Sans ce verrou, une file chargée verrait plusieurs workers traiter
    // les mêmes jobs en parallèle et envoyer des emails en double.
    ->withoutOverlapping()
    ->runInBackground();

/*
| Purge des jobs échoués de plus de 30 jours : sur mutualisé, l'espace
| disque est compté, et une table qui ne cesse d'enfler finit par gêner.
*/
Schedule::command('queue:prune-failed', ['--hours=720'])->weekly();
