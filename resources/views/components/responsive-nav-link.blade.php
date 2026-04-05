@props(['active'])

@php
$classes = ($active ?? false)
 ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-primary text-start text-base font-bold text-primary bg-primary-fixed focus:outline-none transition duration-150 ease-in-out'
 : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-on-surface-variant hover:text-on-surface hover:bg-surface-bright hover:border-outline-variant/30 focus:outline-none focus:text-on-surface focus:bg-surface-container-low/50 focus:border-outline-variant/30 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
 {{ $slot }}
</a>
