/*
 * Bouton d'impression de la facture.
 *
 * Fichier externe volontairement, et non un onclick= : la CSP appliquée en
 * production (script-src 'self' 'unsafe-eval') n'autorise pas les
 * gestionnaires d'événements en ligne, qui échouent alors silencieusement.
 * La facture étant une page autonome — ni Vite ni Alpine, pour ne pas
 * polluer la mise en page d'impression —, ce script minimal fait le travail.
 */
document.addEventListener('DOMContentLoaded', function () {
    var button = document.querySelector('[data-print]');

    if (button) {
        button.addEventListener('click', function () {
            window.print();
        });
    }
});
