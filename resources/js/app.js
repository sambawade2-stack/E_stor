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
