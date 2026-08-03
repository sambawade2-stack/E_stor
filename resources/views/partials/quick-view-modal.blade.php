{{--
    Modale globale « Aperçu rapide ». Un seul exemplaire dans le DOM,
    peu importe le nombre de cartes produit sur la page.
    Ouverture : window.dispatchEvent(new CustomEvent('open-quick-view', { detail: { url } }))
--}}
<div x-data="{
        open: false,
        loading: false,
        html: '',
        async load(url) {
            this.loading = true;
            this.html = '';
            this.open = true;
            try {
                const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                this.html = await res.text();
            } catch (e) {
                this.html = '<p class=\'p-6 text-sm text-gray-500\'>Impossible de charger l\'aperçu pour le moment.</p>';
            } finally {
                this.loading = false;
            }
        },
     }"
     @open-quick-view.window="load($event.detail.url)"
     x-show="open" x-cloak
     @keydown.escape.window="open = false"
     x-effect="document.documentElement.classList.toggle('overflow-hidden', open)"
     class="fixed inset-0 z-[60] flex items-center justify-center p-4"
     role="dialog" aria-modal="true" aria-label="Aperçu rapide du produit">

    {{-- Overlay --}}
    <div x-show="open" x-transition:enter="transition duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition duration-200" x-transition:leave-end="opacity-0"
         @click="open = false" class="absolute inset-0 bg-gray-950/60 backdrop-blur-sm"></div>

    {{-- Panneau --}}
    <div x-show="open"
         x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition duration-200 ease-in" x-transition:leave-end="opacity-0 scale-95"
         class="relative max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-3xl bg-white p-6 shadow-2xl sm:p-8">

        <button @click="open = false" aria-label="Fermer"
                class="absolute right-4 top-4 z-10 grid h-9 w-9 place-items-center rounded-full bg-gray-100 text-gray-500 transition hover:bg-gray-200 hover:text-gray-700">
            <svg class="h-4.5 w-4.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
        </button>

        {{-- Squelette de chargement --}}
        <div x-show="loading" class="grid animate-pulse gap-6 sm:grid-cols-2">
            <div class="img-skeleton aspect-square rounded-2xl"></div>
            <div class="space-y-3 pt-2">
                <div class="h-3 w-24 rounded-full bg-gray-100"></div>
                <div class="h-5 w-3/4 rounded-full bg-gray-100"></div>
                <div class="h-4 w-32 rounded-full bg-gray-100"></div>
                <div class="h-8 w-28 rounded-full bg-gray-100"></div>
                <div class="mt-4 h-12 w-full rounded-full bg-gray-100"></div>
            </div>
        </div>

        <div x-show="!loading" x-html="html"></div>
    </div>
</div>
