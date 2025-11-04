<x-app-layout>
    <x-slot name="title">QuizBall - Αναζήτηση Αντιπάλου</x-slot>

    @push('head')
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
        <meta http-equiv="Pragma" content="no-cache">
        <meta http-equiv="Expires" content="0">
    @endpush

    <div class="bg-gradient-to-br p-8 from-indigo-50 via-white to-purple-50 py-8 flex items-center justify-center h-full">
        <div class="max-w-2xl p-8">
            <div class="text-center mb-10">
                <div class="inline-block mb-4">
                    <div
                        class="w-20 h-20 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white text-4xl animate-pulse">
                        🔍
                    </div>
                </div>
                <h1 class="text-4xl font-black text-gray-900 mb-3">
                    Ψάχνουμε τον <span
                        class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600">Αντίπαλο </span>σου
                </h1>
                <p class="text-lg text-gray-600">Κάντε υπομονή! Σας βρίσκουμε αντίπαλο...</p>
                <p class="text-lg text-red-600 font-bold">Μην κλείσετε την σελίδα!</p>
            </div>

            <div class=" rounded-2xl  p-8  mb-6">
                <div class="flex justify-center gap-2">
                    <div class="w-3 h-3 bg-blue-600 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                    <div class="w-3 h-3 bg-purple-600 rounded-full animate-bounce"
                         style="animation-delay: 150ms"></div>
                    <div class="w-3 h-3 bg-pink-600 rounded-full animate-bounce"
                         style="animation-delay: 300ms"></div>
                </div>
            </div>

            <form method="POST" action="{{ route('game.cancelMatchmaking', $game) }}" class="text-center">
                @csrf
                <button type="submit"
                        class="px-6 py-3 bg-red-600 border-2 border-red-500 hover:border-red-500  text-white font-semibold rounded-xl transition-all duration-200">
                    Ακύρωση Αντιστοίχισης
                </button>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            let matchFound = false;
            let channel = null;

            function initializeMatchmaking() {
                channel = window.Echo.private('game.{{ $game->id }}')
                    .listen('MatchFound', (event) => {
                        matchFound = true;

                        if (event.redirect_url) {
                            window.location.href = event.redirect_url;
                        }
                    })
                    .error((error) => {
                        console.error('Echo subscription error:', error);
                    });

                console.log('Subscribed to game.{{ $game->id }} channel');
            }

            function cancelMatchmaking() {
                if (!matchFound) {
                    const formData = new FormData();
                    formData.append('_token', '{{ csrf_token() }}');
                    navigator.sendBeacon('{{ route('game.cancelMatchmaking', $game) }}', formData);
                }
            }

            function cleanup() {
                if (channel) {
                    window.Echo.leave('game.{{ $game->id }}');
                    channel = null;
                }
            }

            document.addEventListener('click', (event) => {
                const link = event.target.closest('a');
                if (link && link.href && !link.target) {
                    cleanup();
                    cancelMatchmaking();
                }
            });

            window.addEventListener('beforeunload', () => {
                cleanup();
            });

            window.addEventListener('pagehide', () => {
                cleanup();
            });

            // Initialize when page loads - wait for Echo to be ready
            function waitForEcho() {
                if (window.Echo) {
                    initializeMatchmaking();
                } else {
                    console.log('Waiting for Echo to initialize...');
                    setTimeout(waitForEcho, 100);
                }
            }

            // Start waiting for Echo after DOM is ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', waitForEcho);
            } else {
                waitForEcho();
            }
        </script>
    @endpush
</x-app-layout>
