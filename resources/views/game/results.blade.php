<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Game Results
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <!-- Winner Display -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-8 text-center">
                    @if($winner->id === $player->id)
                        <div class="text-yellow-500">
                            <div class="text-7xl mb-4">🏆</div>
                            <h2 class="text-4xl font-bold mb-2">Victory!</h2>
                            <p class="text-xl text-gray-600">Congratulations, you won the game!</p>
                        </div>
                    @else
                        <div class="text-blue-500">
                            <div class="text-7xl mb-4">🎯</div>
                            <h2 class="text-4xl font-bold mb-2">Good Game!</h2>
                            <p class="text-xl text-gray-600">{{ $winner->display_name }} won this time!</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Final Scores -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Final Scores</h3>
                    <div class="grid grid-cols-2 gap-4">
                        @foreach($game->players as $p)
                            <div class="p-4 rounded-lg {{ $p->id === $winner->id ? 'bg-yellow-50 ring-2 ring-yellow-400' : 'bg-gray-50' }}">
                                <div class="text-sm text-gray-600">{{ $p->display_name }}</div>
                                <div class="text-3xl font-bold text-gray-900">{{ $p->score }}</div>
                                @if($p->id === $winner->id)
                                    <div class="text-sm text-yellow-600 font-semibold">Winner!</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Round by Round Breakdown -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Round by Round</h3>
                    <div class="space-y-3">
                        @php
                            $playerRounds = $game->rounds->where('game_player_id', $player->id);
                        @endphp
                        @foreach($playerRounds as $round)
                            <div class="border rounded-lg p-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <div class="font-semibold text-gray-900">
                                            Round {{ $round->round_number }}: {{ $round->category->name }}
                                        </div>
                                        <div class="text-sm text-gray-600 mt-1">
                                            {{ $round->question->question_text }}
                                        </div>
                                        <div class="text-sm text-gray-500 mt-1">
                                            Difficulty: {{ ucfirst($round->difficulty->value) }}
                                        </div>
                                    </div>
                                    <div class="text-right ml-4">
                                        @if($round->is_correct)
                                            <div class="text-green-600 text-3xl">✅</div>
                                            <div class="text-sm font-semibold text-green-600">+{{ $round->points_earned }} pts</div>
                                        @else
                                            <div class="text-red-600 text-3xl">❌</div>
                                            <div class="text-sm font-semibold text-red-600">0 pts</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-4">
                <a href="{{ route('game.lobby') }}" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg text-center transition">
                    Play Again
                </a>
                @auth
                    <a href="{{ route('dashboard') }}" class="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-6 rounded-lg text-center transition">
                        View Dashboard
                    </a>
                @endauth
            </div>
        </div>
    </div>
</x-app-layout>