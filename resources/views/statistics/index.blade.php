<x-app-layout>
    <x-slot name="title">QuizBall - Τα Στατιστικά μου</x-slot>
    <x-slot name="header">
        <x-page-header title="Τα Στατιστικά μου" icon="📊" />
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Overview Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <span class="text-2xl">🎮</span>
                        </div>
                        <div>
                            <div class="text-3xl font-bold text-gray-900">{{ $totalGames }}</div>
                            <div class="text-sm text-gray-600">Συνολικά Παιχνίδια</div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <span class="text-2xl">🏆</span>
                        </div>
                        <div>
                            <div class="text-3xl font-bold text-green-600">{{ $gamesWon }}</div>
                            <div class="text-sm text-gray-600">Νίκες</div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                            <span class="text-2xl">💔</span>
                        </div>
                        <div>
                            <div class="text-3xl font-bold text-red-600">{{ $gamesLost }}</div>
                            <div class="text-sm text-gray-600">Ήττες</div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                            <span class="text-2xl">📈</span>
                        </div>
                        <div>
                            <div class="text-3xl font-bold text-purple-600">{{ $winRate }}%</div>
                            <div class="text-sm text-gray-600">Ποσοστό Νίκης</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Stats Row -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <span class="text-2xl">⭐</span>
                        </div>
                        <div>
                            <div class="text-3xl font-bold text-yellow-600">{{ $totalScore }}</div>
                            <div class="text-sm text-gray-600">Συνολικοί Πόντοι</div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                            <span class="text-2xl">🎯</span>
                        </div>
                        <div>
                            <div class="text-3xl font-bold text-indigo-600">{{ $correctAnswers }}</div>
                            <div class="text-sm text-gray-600">Σωστές Απαντήσεις</div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-pink-100 rounded-lg flex items-center justify-center">
                            <span class="text-2xl">❌</span>
                        </div>
                        <div>
                            <div class="text-3xl font-bold text-pink-600">{{ $incorrectAnswers }}</div>
                            <div class="text-sm text-gray-600">Λάθος Απαντήσεις</div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-teal-100 rounded-lg flex items-center justify-center">
                            <span class="text-2xl">💯</span>
                        </div>
                        <div>
                            <div class="text-3xl font-bold text-teal-600">{{ $accuracy }}%</div>
                            <div class="text-sm text-gray-600">Ακρίβεια</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Best Categories & Recent Opponents -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- VS AI Stats -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">🤖 Παιχνίδια VS AI</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-700">Συνολικά Παιχνίδια</span>
                            <span class="text-2xl font-bold text-blue-600">{{ $gamesVsAI }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-700">Νίκες</span>
                            <span class="text-2xl font-bold text-green-600">{{ $winsVsAI }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-purple-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-700">Ποσοστό Νίκης</span>
                            <span class="text-2xl font-bold text-purple-600">
                                {{ $gamesVsAI > 0 ? round(($winsVsAI / $gamesVsAI) * 100) : 0 }}%
                            </span>
                        </div>
                    </div>
                </div>

                <!-- VS Humans Stats -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">👤 Παιχνίδια VS Παίκτες</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-700">Συνολικά Παιχνίδια</span>
                            <span class="text-2xl font-bold text-blue-600">{{ $gamesVsHumans }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-700">Νίκες</span>
                            <span class="text-2xl font-bold text-green-600">{{ $winsVsHumans }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-purple-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-700">Ποσοστό Νίκης</span>
                            <span class="text-2xl font-bold text-purple-600">
                                {{ $gamesVsHumans > 0 ? round(($winsVsHumans / $gamesVsHumans) * 100) : 0 }}%
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Best Categories & Recent Opponents -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Best Categories -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">🌟 Καλύτερες Κατηγορίες</h3>
                    @if($bestCategories->count() > 0)
                        <div class="space-y-3">
                            @foreach($bestCategories as $category)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="flex items-center gap-3">
                                        <span class="text-2xl">{{ $category->icon }}</span>
                                        <span class="font-medium text-gray-900">{{ $category->name }}</span>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-lg font-bold text-green-600">{{ $category->correct_answers }}</div>
                                        <div class="text-xs text-gray-500">σωστές απαντήσεις</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-8">Καμία σωστή απάντηση ακόμα. Συνέχισε να παίζεις!</p>
                    @endif
                </div>

                <!-- Recent Opponents -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">👥 Συχνοί Αντίπαλοι</h3>
                    @if($recentOpponents->count() > 0)
                        <div class="space-y-3">
                            @foreach($recentOpponents as $opponent)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold">
                                            <img src="{{ \App\Avatar::showAvatar($opponent) }}" alt="{{ $opponent->name }}" class="w-full rounded-full h-full object-cover">
                                        </div>
                                        <span class="font-medium text-gray-900">{{ $opponent->name }}</span>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-lg font-bold text-blue-600">{{ $opponent->games_count }}</div>
                                        <div class="text-xs text-gray-500">παιχνίδια</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-8">Κανένας αντίπαλος ακόμα. Ξεκίνα να παίζεις!</p>
                    @endif
                </div>
            </div>

            <!-- Recent Games -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">📜 Πρόσφατα Παιχνίδια</h3>
                @if($recentGames->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ΗΜΕΡΟΜΗΝΙΑ</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ΑΝΤΙΠΑΛΟΣ</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ΣΚΟΡ</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ΑΠΟΤΕΛΕΣΜΑ</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ΕΝΕΡΓΕΙΕΣ</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($recentGames as $gamePlayer)
                                    @php
                                        $opponent = $gamePlayer->game->gamePlayers->where('user_id', '!=', auth()->id())->first();
                                    @endphp
                                    <tr class="{{ $gamePlayer->is_winner ? 'bg-green-50' : '' }}">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $gamePlayer->created_at->translatedFormat('d M, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $opponent?->user->name ?? $opponent?->guest_name ?? 'Μ/Δ' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm font-bold text-gray-900">{{ $gamePlayer->score }} πόντοι</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($gamePlayer->game->status->value === 'completed')
                                                @if($gamePlayer->is_winner)
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                        🏆 Νίκη
                                                    </span>
                                                @else
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                        Ήττα
                                                    </span>
                                                @endif
                                            @elseif($gamePlayer->game->status->value === 'forfeited')
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                                    Ματαίωση
                                                </span>
                                            @else
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    {{ ucfirst($gamePlayer->game->status->value) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <a href="{{ route('game.results', $gamePlayer->game) }}"
                                               class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition-colors duration-200 text-xs">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                Προβολή
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $recentGames->links() }}
                    </div>
                @else
                    <p class="text-gray-500 text-center py-8">Δεν έχεις παίξει ακόμα. <a href="{{ route('game.lobby') }}" class="text-blue-600 hover:text-blue-800 font-medium">Ξεκίνα να παίζεις!</a></p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
