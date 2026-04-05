<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-5 py-2.5 bg-error border border-transparent rounded-xl font-bold text-sm text-white hover:bg-red-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500/30 transition ease-in-out duration-150']) }}>
 {{ $slot }}
</button>
