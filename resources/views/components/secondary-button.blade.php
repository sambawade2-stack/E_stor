<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center gap-2 rounded-full border-2 border-gray-200 bg-white px-6 py-2.5 text-sm font-semibold text-gray-700 transition duration-300 hover:-translate-y-0.5 hover:border-primary-300 hover:text-primary-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2 active:translate-y-0 active:scale-[0.98] disabled:pointer-events-none disabled:opacity-50']) }}>
    {{ $slot }}
</button>
