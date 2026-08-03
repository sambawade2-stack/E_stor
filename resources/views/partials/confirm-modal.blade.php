{{--
    Modale de confirmation globale, utilisée par le composant
    <x-confirm-form> pour remplacer les confirm() natifs du navigateur.
    Un seul exemplaire dans le DOM par page.
--}}
<div x-data="{ open: false, message: '', form: null }"
     @open-confirm.window="open = true; message = $event.detail.message; form = $event.detail.form"
     @keydown.escape.window="open = false"
     x-show="open" x-cloak
     class="fixed inset-0 z-[70] flex items-center justify-center p-4"
     role="alertdialog" aria-modal="true" aria-label="Confirmation">

    <div x-show="open" x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition duration-150" x-transition:leave-end="opacity-0"
         @click="open = false" class="absolute inset-0 bg-gray-950/60 backdrop-blur-sm"></div>

    <div x-show="open"
         x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition duration-150 ease-in" x-transition:leave-end="opacity-0 scale-95"
         class="relative w-full max-w-sm rounded-3xl bg-white p-6 text-center shadow-2xl">

        <span class="mx-auto mb-4 grid h-14 w-14 place-items-center rounded-full bg-danger-50 text-danger-500">
            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-8.625 5.25h.008v.008h-.008v-.008Z"/>
            </svg>
        </span>

        <h2 class="font-display text-lg font-bold text-gray-900">Confirmer l'action</h2>
        <p class="mt-2 text-sm leading-relaxed text-gray-500" x-text="message"></p>

        <div class="mt-6 flex gap-3">
            <button type="button" @click="open = false"
                    class="flex-1 rounded-full border-2 border-gray-200 py-2.5 text-sm font-semibold text-gray-600 transition hover:bg-gray-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2">
                Annuler
            </button>
            <button type="button" @click="form.submit(); open = false"
                    class="flex-1 rounded-full bg-danger-600 py-2.5 text-sm font-semibold text-white shadow-lg shadow-danger-600/25 transition hover:bg-danger-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-danger-500 focus-visible:ring-offset-2">
                Confirmer
            </button>
        </div>
    </div>
</div>
