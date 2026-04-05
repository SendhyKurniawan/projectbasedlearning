@props(['active'])

@php
$classes = ($active ?? false)
 ? 'inline-flex items-center px-1 pt-1 border-b-2 border-primary text-sm font-bold leading-5 text-on-surface focus:outline-none transition duration-150 ease-in-out'
 : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-on-surface-variant hover:text-on-surface hover:border-outline-variant/30 focus:outline-none focus:text-on-surface-variant focus:border-outline-variant/30 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
 {{ $slot }}
</a>
