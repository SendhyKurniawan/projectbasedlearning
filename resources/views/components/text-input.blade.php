@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-none bg-surface-container-highest text-on-surface focus:ring-2 focus:ring-primary/20 rounded-xl placeholder:text-outline/60 text-sm py-3 px-4']) }}>
