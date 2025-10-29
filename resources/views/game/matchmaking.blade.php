<x-app-layout>
    <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50 py-8">
        <div class="max-w-2xl mx-auto px-6">
            <!-- Matchmaking Header -->
            <div class="text-center mb-10">
                <div class="inline-block mb-4">
                    <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white text-4xl animate-pulse">
                        🔍
                    </div>
                </div>
                <h1 class="text-4xl font-black text-gray-900 mb-3">
                    Finding Your <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600">Opponent</span>
                </h1>
                <p class="text-lg text-gray-600">Hang tight! We're matching you with another player...</p>
            </div>

            <!-- Queue Status Card -->
            <div class="bg-white rounded-2xl border-2 border-gray-100 p-8 shadow-lg mb-6">
                <div class="text-center space-y-6">
                    <!-- Animated Dots -->
                    <div class="flex justify-center gap-2">
                        <div class="w-3 h-3 bg-blue-600 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                        <div class="w-3 h-3 bg-purple-600 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                        <div class="w-3 h-3 bg-pink-600 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                    </div>

                    <!-- Queue Position -->
                    <div class="space-y-2">
                        <div class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Queue Position</div>
                        <div id="queue-position" class="text-5xl font-black text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600">
                            {{ $queuePosition }}
                        </div>
                        <div class="text-sm text-gray-500">
                            <span id="queue-ahead">{{ $queuePosition > 1 ? ($queuePosition - 1) . ' player(s) ahead of you' : 'You\'re next!' }}</span>
                        </div>
                    </div>

                    <!-- Player Info -->
                    <div class="pt-4 border-t border-gray-200">
                        <div class="text-sm text-gray-600">You're playing as</div>
                        <div class="text-xl font-bold text-gray-900 mt-1">
                            {{ $player->user?->name ?? $player->guest_name }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tips Card -->
            <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-2xl border-2 border-amber-200 p-6 mb-6">
                <div class="flex items-start gap-3">
                    <div class="text-2xl">💡</div>
                    <div>
                        <h3 class="font-bold text-gray-900 mb-1">While You Wait...</h3>
                        <p class="text-sm text-gray-600">
                            Remember: Pick wisely! Each category-difficulty combo can only be used once. 
                            Hard questions are worth more points but are tougher to answer!
                        </p>
                    </div>
                </div>
            </div>

            <!-- Cancel Button -->
            <form method="POST" action="{{ route('game.cancelMatchmaking', $game) }}" class="text-center">
                @csrf
                <button type="submit" class="px-6 py-3 bg-white border-2 border-gray-200 hover:border-red-500 hover:text-red-600 text-gray-700 font-semibold rounded-xl transition-all duration-200">
                    Cancel Matchmaking
                </button>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        // Poll for opponent every 2 seconds
        const checkInterval = setInterval(async () => {
            try {
                const response = await fetch('{{ route('game.checkMatchmaking', $game) }}');
                const data = await response.json();
                
                // Update queue position
                document.getElementById('queue-position').textContent = data.queue_position;
                if (data.queue_position > 1) {
                    document.getElementById('queue-ahead').textContent = `${data.queue_position - 1} player(s) ahead of you`;
                } else {
                    document.getElementById('queue-ahead').textContent = "You're next!";
                }
                
                // If opponent found, redirect to game
                if (data.found && data.redirect_url) {
                    clearInterval(checkInterval);
                    window.location.href = data.redirect_url;
                }
            } catch (error) {
                console.error('Error checking matchmaking:', error);
            }
        }, 2000);

        // Clear interval when page unloads
        window.addEventListener('beforeunload', () => {
            clearInterval(checkInterval);
        });
    </script>
    @endpush
</x-app-layout>
