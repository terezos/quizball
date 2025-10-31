@props(['title', 'icon' => null])

<div class="flex flex-col sm:flex-row gap-3 sm:justify-between sm:items-center">
    <div class="flex items-center gap-3">
        @if($icon)
            <div class="hidden sm:flex w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl items-center justify-center shadow-md">
                <span class="text-2xl">{{ $icon }}</span>
            </div>
        @endif
        <h2 class="font-bold text-2xl text-gray-900">
            {{ $title }}
        </h2>
    </div>

    @if(isset($actions))
        <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
            {{ $actions }}
        </div>
    @endif
</div>
