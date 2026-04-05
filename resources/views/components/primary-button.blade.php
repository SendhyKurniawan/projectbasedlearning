<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-5 py-2.5 architectural-gradient border border-transparent rounded-xl font-bold text-sm text-white tracking-wide shadow-lg shadow-primary/20 hover:shadow-primary/40 active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all duration-150']) }}>
 {{ $slot }}
</button>
