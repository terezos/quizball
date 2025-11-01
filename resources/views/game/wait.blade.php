<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Αναμονή για Αντίπαλο
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ polling: true }" x-init="
        setInterval(async () => {
            const response = await fetch('/game/{{ $game->id }}/state');
            const data = await response.json();
            if (data.status === 'active') {
                window.location.href = '/game/{{ $game->id }}/play';
            }
        }, 2000);
    ">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8 text-center">
                    <div class="mb-6">
                        <div class="animate-bounce text-6xl mb-4">⚽</div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Αναμονή για Αντίπαλο...</h3>
                        <p class="text-gray-600">Μοιράσου αυτόν τον κωδικό με έναν φίλο για να συμμετάσχει στο παιχνίδι</p>
                    </div>

                    <div class="bg-blue-50 p-8 rounded-lg mb-6">
                        <div class="text-sm text-gray-600 mb-2">Κωδικός Παιχνιδιού</div>
                        <div class="text-5xl font-bold text-blue-600 tracking-widest mb-4">{{ $game->game_code }}</div>
                        <button onclick="navigator.clipboard.writeText('{{ $game->game_code }}')"
                                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition">
                            📋 Αντιγραφή Κωδικού
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
