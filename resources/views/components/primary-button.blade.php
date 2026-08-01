<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 rounded-full bg-gradient-to-r from-primary-600 to-accent-500 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary-600/30 transition duration-300 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-primary-500/40 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2 active:translate-y-0 active:scale-[0.98] disabled:pointer-events-none disabled:opacity-50']) }}>
    {{ $slot }}
</button>
