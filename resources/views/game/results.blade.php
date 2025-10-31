<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Game Results
        </h2>
    </x-slot>

    <div class="py-8 px-4">
        <div class="max-w-6xl mx-auto">
            @php
                $opponent = $game->players->firstWhere('id', '!=', $player->id);
                $iWon = $winner->id === $player->id;
            @endphp

            <!-- Winner Announcement - Minimal -->
            <div class="text-center mb-8">
                <div class="text-6xl mb-3">{{ $iWon ? '🏆' : '👏' }}</div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">
                    {{ $iWon ? 'You Won!' : 'Good Game!' }}
                </h1>
                <p class="text-gray-600">{{ $iWon ? 'Congratulations!' : $winner->display_name . ' won this round' }}</p>
            </div>

            <!-- Score Comparison - Compact -->
            <div class="grid grid-cols-2 gap-4 mb-8 max-w-md mx-auto">
                <div class="text-center p-4 rounded-xl {{ $player->id === $winner->id ? 'bg-gradient-to-br from-yellow-50 to-amber-50 ring-2 ring-yellow-400' : 'bg-gray-50' }}">
                    <div class="text-sm font-medium text-gray-600 mb-1">You</div>
                    <div class="text-4xl font-bold text-gray-900">{{ $player->score }}</div>
                </div>
                <div class="text-center p-4 rounded-xl {{ $opponent->id === $winner->id ? 'bg-gradient-to-br from-yellow-50 to-amber-50 ring-2 ring-yellow-400' : 'bg-gray-50' }}">
                    <div class="text-sm font-medium text-gray-600 mb-1">{{ $opponent->display_name }}</div>
                    <div class="text-4xl font-bold text-gray-900">{{ $opponent->score }}</div>
                </div>
            </div>

            <!-- Round by Round - Side by Side -->
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-900 mb-4 text-center">Round Breakdown</h2>

                <div class="space-y-6">
                    @php
                        // Group rounds by round number
                        $roundsByNumber = $game->rounds->groupBy('round_number')->sortKeys();
                    @endphp

                    @foreach($roundsByNumber as $roundNumber => $rounds)
                        @php
                            $myRound = $rounds->firstWhere('game_player_id', $player->id);
                            $opponentRound = $rounds->firstWhere('game_player_id', $opponent->id);

                            // Use whichever round exists to get question details
                            $round = $myRound ?? $opponentRound;

                            // Get correct answer
                            $correctAnswer = $round->question->answers->where('is_correct', true)->first()?->answer_text;
                            if ($round->question->question_type->value === 'top_5') {
                                $correctAnswer = $round->question->answers->where('is_correct', true)->take(5)->pluck('answer_text')->implode(', ');
                            }
                        @endphp

                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                            <!-- Question Header -->
                            <div class="bg-gradient-to-r from-gray-50 to-slate-50 p-4 border-b border-gray-200">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-200 text-gray-800">
                                                Round {{ $roundNumber }}
                                            </span>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $round->difficulty->value === 'easy' ? 'bg-green-100 text-green-800' : ($round->difficulty->value === 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                {{ ucfirst($round->difficulty->value) }}
                                            </span>
                                            <span class="text-sm text-gray-600">{{ $round->category->icon }} {{ $round->category->name }}</span>
                                        </div>
                                        <p class="text-gray-900 font-medium">{{ $round->question->question_text }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Answers Side by Side -->
                            <div class="grid md:grid-cols-2 divide-x divide-gray-200">
                                <!-- My Answer -->
                                <div class="p-4 {{ $myRound && $myRound->is_correct ? 'bg-green-50' : ($myRound ? 'bg-red-50' : 'bg-gray-50') }}">
                                    <div class="flex items-start justify-between mb-3">
                                        <span class="text-sm font-semibold text-gray-900">You</span>
                                        @if($myRound)
                                            <div class="flex items-center gap-2">
                                                <span class="text-2xl">{{ $myRound->is_correct ? '✓' : '×' }}</span>
                                                <span class="text-sm font-bold {{ $myRound->is_correct ? 'text-green-700' : 'text-red-700' }}">
                                                    {{ $myRound->is_correct ? '+' . $myRound->points_earned : '0' }} pts
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-sm text-gray-500 italic">Skipped</span>
                                        @endif
                                    </div>
                                    @if($myRound)
                                        @php
                                            $myAnswer = $myRound->player_answer;
                                            if ($myRound->question->question_type->value === 'multiple_choice') {
                                                $answerId = json_decode($myAnswer, true) ?? $myAnswer;
                                                $myAnswer = $myRound->question->answers->find($answerId)?->answer_text ?? 'No answer';
                                            } elseif ($myRound->question->question_type->value === 'top_5') {
                                                $answers = json_decode($myAnswer, true);
                                                $myAnswer = is_array($answers) ? implode(', ', $answers) : $myAnswer;
                                            }
                                        @endphp
                                        <div class="text-sm font-medium text-gray-900 mb-1">Your answer:</div>
                                        <div class="text-sm text-gray-700 break-words">{{ $myAnswer }}</div>
                                    @endif
                                </div>

                                <!-- Opponent Answer -->
                                <div class="p-4 {{ $opponentRound && $opponentRound->is_correct ? 'bg-green-50' : ($opponentRound ? 'bg-red-50' : 'bg-gray-50') }}">
                                    <div class="flex items-start justify-between mb-3">
                                        <span class="text-sm font-semibold text-gray-900">{{ $opponent->display_name }}</span>
                                        @if($opponentRound)
                                            <div class="flex items-center gap-2">
                                                <span class="text-2xl">{{ $opponentRound->is_correct ? '✓' : '×' }}</span>
                                                <span class="text-sm font-bold {{ $opponentRound->is_correct ? 'text-green-700' : 'text-red-700' }}">
                                                    {{ $opponentRound->is_correct ? '+' . $opponentRound->points_earned : '0' }} pts
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-sm text-gray-500 italic">Skipped</span>
                                        @endif
                                    </div>
                                    @if($opponentRound)
                                        @php
                                            $opponentAnswer = $opponentRound->player_answer;
                                            if ($opponentRound->question->question_type->value === 'multiple_choice') {
                                                $answerId = json_decode($opponentAnswer, true) ?? $opponentAnswer;
                                                $opponentAnswer = $opponentRound->question->answers->find($answerId)?->answer_text ?? 'No answer';
                                            } elseif ($opponentRound->question->question_type->value === 'top_5') {
                                                $answers = json_decode($opponentAnswer, true);
                                                $opponentAnswer = is_array($answers) ? implode(', ', $answers) : $opponentAnswer;
                                            }
                                        @endphp
                                        <div class="text-sm font-medium text-gray-900 mb-1">Their answer:</div>
                                        <div class="text-sm text-gray-700 break-words">{{ $opponentAnswer }}</div>
                                    @endif
                                </div>
                            </div>

                            <!-- Correct Answer Footer -->
                            @if($correctAnswer && (($myRound && !$myRound->is_correct) || ($opponentRound && !$opponentRound->is_correct)))
                                <div class="bg-gradient-to-r from-emerald-50 to-green-50 p-3 border-t border-green-200">
                                    <div class="flex items-center gap-2 text-sm">
                                        <span class="font-semibold text-green-800">Correct answer:</span>
                                        <span class="text-green-900">{{ $correctAnswer }}</span>
                                    </div>
                                </div>
                            @endif

                            <!-- Source Link -->
                            @if($round->question->source_url)
                                <div class="bg-gray-50 px-4 py-2 border-t border-gray-200">
                                    <a href="{{ $round->question->source_url }}" target="_blank" class="text-xs text-blue-600 hover:text-blue-800 hover:underline flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                        </svg>
                                        Verify source
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
                <a href="{{ route('game.lobby') }}" class="flex-1 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold py-3 px-6 rounded-xl text-center transition-all duration-200 shadow-md hover:shadow-lg">
                    Play Again
                </a>
                @auth
                    <a href="{{ route('dashboard') }}" class="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-semibold py-3 px-6 rounded-xl text-center transition-all duration-200 shadow-md hover:shadow-lg">
                        Dashboard
                    </a>
                @endauth
            </div>
        </div>
    </div>
</x-app-layout>