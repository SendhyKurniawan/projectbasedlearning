<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-5 py-2.5 bg-surface-container-high border border-transparent rounded-xl font-bold text-sm text-on-surface-variant hover:bg-surface-container-highest focus:outline-none focus:ring-2 focus:ring-primary/20 disabled:opacity-25 transition ease-in-out duration-150']) }}>
 {{ $slot }}
</button>
