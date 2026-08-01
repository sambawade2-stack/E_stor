<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 rounded-full bg-danger-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-danger-600/25 transition duration-300 hover:-translate-y-0.5 hover:bg-danger-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-danger-500 focus-visible:ring-offset-2 active:translate-y-0 active:scale-[0.98] disabled:pointer-events-none disabled:opacity-50']) }}>
    {{ $slot }}
</button>
