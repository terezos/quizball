@props(['active', 'icon' => null])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-blue-50 to-purple-50 text-blue-600 font-semibold transition-all duration-200 shadow-sm'
            : 'inline-flex items-center gap-2 px-4 py-2 rounded-xl text-gray-700 font-medium hover:bg-gray-50 hover:text-gray-900 transition-all duration-200';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    @if($icon)
        <span class="text-lg">{{ $icon }}</span>
    @endif
    {{ $slot }}
</a>
