<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-5 py-2.5 bg-error border border-transparent rounded-xl font-bold text-sm text-on-error hover:bg-error/90 active:bg-error/80 focus:outline-none focus:ring-2 focus:ring-error/30 transition ease-in-out duration-150']) }}>
 {{ $slot }}
</button>
