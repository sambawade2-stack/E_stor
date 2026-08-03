{{--
    Barre de progression de navigation (façon NProgress) : donne un
    retour visuel immédiat sur clic de lien ou soumission de formulaire,
    avant même que la nouvelle page n'arrive — utile en particulier pour
    les filtres catalogue et formulaires (checkout, admin) qui rechargent
    la page. Purement cosmétique, pilotée par resources/js/app.js.
--}}
<div id="nav-progress" class="pointer-events-none fixed inset-x-0 top-0 z-[100] h-[3px] w-0 bg-gradient-to-r from-primary-500 via-accent-400 to-primary-500 opacity-0"></div>
