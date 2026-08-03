import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

/* ------------------------------------------------------------------ */
/* Animations d'apparition au scroll                                   */
/* Active le masquage initial (classe .has-anim) puis révèle chaque    */
/* élément [data-animate] à son entrée dans le viewport.               */
/* ------------------------------------------------------------------ */
document.documentElement.classList.add('has-anim');

const revealObserver = new IntersectionObserver(
    (entries) => {
        for (const entry of entries) {
            if (entry.isIntersecting) {
                entry.target.classList.add('in-view');
                revealObserver.unobserve(entry.target);
            }
        }
    },
    { threshold: 0.12, rootMargin: '0px 0px -40px 0px' }
);

document.querySelectorAll('[data-animate]').forEach((el) => revealObserver.observe(el));

/* ------------------------------------------------------------------ */
/* Barre de progression de navigation                                  */
/* Retour visuel immédiat sur toute navigation classique (lien ou      */
/* formulaire) : les filtres, le checkout et l'admin rechargent la     */
/* page en GET/POST, ce petit indicateur évite l'impression de blocage.*/
/* ------------------------------------------------------------------ */
const navProgress = document.getElementById('nav-progress');

if (navProgress) {
    const startNavProgress = () => {
        navProgress.style.transition = 'none';
        navProgress.style.width = '0%';
        navProgress.style.opacity = '1';
        // Force le recalcul de style avant de relancer la transition
        void navProgress.offsetWidth;
        navProgress.style.transition = 'width 700ms cubic-bezier(0.1, 0.9, 0.2, 1)';
        navProgress.style.width = '80%';
    };

    document.addEventListener('click', (event) => {
        const link = event.target.closest('a[href]');

        if (!link || link.target === '_blank' || link.hasAttribute('download')) return;
        if (event.metaKey || event.ctrlKey || event.shiftKey || event.button !== 0) return;
        if (link.origin !== window.location.origin) return;
        if (link.getAttribute('href').startsWith('#')) return;

        startNavProgress();
    });

    document.addEventListener('submit', (event) => {
        if (event.target instanceof HTMLFormElement) startNavProgress();
    });

    // Nouvelle page chargée (y compris retour arrière depuis le cache) : on masque la barre
    window.addEventListener('pageshow', () => {
        navProgress.style.transition = 'none';
        navProgress.style.width = '0%';
        navProgress.style.opacity = '0';
    });
}
