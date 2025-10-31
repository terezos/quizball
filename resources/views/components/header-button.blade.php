@props(['variant' => 'primary', 'icon' => null])

@php
    $classes = match($variant) {
        'primary' => 'bg-blue-600 hover:bg-blue-700 text-white',
        'secondary' => 'bg-gray-100 hover:bg-gray-200 text-gray-700',
        'warning' => 'bg-yellow-200 hover:bg-yellow-300 text-black',
        'success' => 'bg-green-600 hover:bg-green-700 text-white',
        'danger' => 'bg-red-600 hover:bg-red-700 text-white',
        default => 'bg-blue-600 hover:bg-blue-700 text-white'
    };
@endphp

<a {{ $attributes->merge(['class' => "w-full sm:w-auto inline-flex items-center justify-center gap-2 font-semibold py-2.5 px-5 rounded-xl transition-all duration-200 shadow-sm hover:shadow-md {$classes}"]) }}>
    @if($icon)
        <span class="text-sm">{{ $icon }}</span>
    @endif
    {{ $slot }}
</a>
