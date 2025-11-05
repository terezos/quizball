<x-app-layout>
    <x-slot name="title">QuizBall - Αποτελέσματα Παιχνιδιού</x-slot>
    <div class="py-6 px-4">

        <div class="max-w-5xl mx-auto @if($game->rounds->count() <= 0 ) mt-8 @endif">
            @php
                $opponent = $game->players->firstWhere('id', '!=', $player->id);
                $iWon = $winner && $winner->id === $player->id;
            @endphp

            <div class="text-center mb-8">
                <div class="text-6xl mb-4">{{ $isDraw ? '🤝' : ($iWon ? '🏆' : '') }}</div>
                <h1 class="text-4xl font-bold text-slate-900 mb-2">
                    {{ $isDraw ? 'Ισοπαλία!' : ($iWon ? 'Νίκησες!' : 'Έχασες!') }}
                </h1>
            </div>

            @if($game->is_forfeited)
                <div class="max-w-md mx-auto mb-8">
                    <div class="bg-gradient-to-r from-red-50 to-orange-50 border-2 border-red-300 rounded-xl p-6 shadow-lg">
                        <div class="flex items-center gap-4">
                            <div class="text-5xl">⚠️</div>
                            <div class="flex-1">
                                <div class="text-lg font-black text-red-900 mb-1">Παιχνίδι με Παραίτηση</div>
                                <div class="text-sm text-red-700 font-semibold">
                                    @php
                                        $forfeiter = $game->players->firstWhere('id', $game->forfeited_by_player_id);
                                    @endphp
                                    @if($forfeiter)
                                        {{ $forfeiter->id === $player->id ? 'Παραιτήθηκες από το παιχνίδι' : ($forfeiter->display_name . ' παραιτήθηκε από το παιχνίδι') }}
                                    @else
                                        Ένας παίκτης παραιτήθηκε από το παιχνίδι
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-2 gap-5 mb-8 max-w-md mx-auto">
                <div class="text-center p-5 rounded-xl {{ $isDraw ? 'bg-gradient-to-br from-blue-50 to-indigo-50 ring-2 ring-blue-300' : ($winner && $player->id === $winner->id ? 'bg-gradient-to-br from-amber-50 to-green-50 ring-2 ring-green-300' : 'bg-slate-100') }}">
                    <div class="text-sm font-semibold text-slate-600 mb-2">ΕΣΥ</div>
                    <div class="text-5xl font-bold text-slate-900">{{ $player->score }}</div>
                </div>
                <div class="text-center p-5 rounded-xl {{ $isDraw ? 'bg-gradient-to-br from-blue-50 to-indigo-50 ring-2 ring-blue-300' : ($winner && $opponent->id === $winner->id ? 'bg-gradient-to-br from-amber-50 to-green-50 ring-2 ring-green-300' : 'bg-slate-100') }}">
                    <div class="text-sm font-semibold text-slate-600 mb-2">{{ $opponent->display_name }}</div>
                    <div class="text-5xl font-bold text-slate-900">{{ $opponent->score }}</div>
                </div>
            </div>

            <div class="flex justify-center mb-8">
                <a href="{{ route('game.lobby') }}" class="bg-gradient-to-r from-indigo-500 to-purple-500 hover:from-indigo-600 hover:to-purple-600 text-white font-semibold py-5 px-10 rounded-xl text-center text-lg transition-all duration-200 shadow-md hover:shadow-lg">
                    Παίξε ξανά
                </a>
            </div>

            @if($game->rounds->count())
            <div class="mb-8">
                <h2 class="font-bold text-slate-800 mb-6 text-center text-3xl">Ανάλυση Γύρων</h2>

                <div class="space-y-5">
                    @php
                        $roundsByNumber = $game->rounds->groupBy('round_number')->sortKeys();
                    @endphp

                    @foreach($roundsByNumber as $roundNumber => $rounds)
                        @php
                            $myRound = $rounds->firstWhere('game_player_id', $player->id);
                            $opponentRound = $rounds->firstWhere('game_player_id', $opponent->id);
                            $round = $myRound ?? $opponentRound;

                            $correctAnswer = $round->question->answers->where('is_correct', true)->first()?->answer_text;
                            if ($round->question->question_type->value === 'top_5') {
                                $correctAnswer = $round->question->answers->where('is_correct', true)->take(5)->pluck('answer_text')->implode(', ');
                            }
                        @endphp

                        <div class="bg-white rounded-xl shadow-md border border-slate-200 overflow-hidden">
                            <!-- Question Header -->
                            <div class="bg-gradient-to-r from-slate-50 to-gray-50 p-4 border-b border-slate-200">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-3 flex-wrap">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-200 text-slate-700">
                                                Γύρος {{ $roundNumber }}
                                            </span>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $round->difficulty->value === 'easy' ? 'bg-emerald-100 text-emerald-800' : ($round->difficulty->value === 'medium' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                                {{ ucfirst($round->difficulty->label()) }}
                                            </span>
                                            <span class="text-sm font-medium text-slate-600">{{ $round->category->icon }} {{ $round->category->name }}</span>
                                            @if($myRound && $myRound->used_5050_powerup)
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">
                                                    🎯 50/50
                                                </span>
                                            @endif
                                            @if($myRound && $myRound->used_2x_powerup)
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-orange-100 text-orange-800">
                                                    ⚡ 2x Πόντοι
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-base text-slate-900 font-semibold leading-relaxed">{{ $round->question->question_text }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="grid md:grid-cols-2 divide-x divide-slate-200">
                                <div class="p-4 {{ $myRound && $myRound->is_correct ? 'bg-emerald-50' : ($myRound ? 'bg-rose-50' : 'bg-slate-50') }}">
                                    <div class="flex items-start justify-between mb-3">
                                        <span class="text-sm font-bold text-slate-900">Εσύ</span>
                                        @if($myRound)
                                            <div class="flex items-center gap-2">
                                                <span class="text-2xl">{{ $myRound->is_correct ? '✓' : '×' }}</span>
                                                <span class="text-sm font-bold {{ $myRound->is_correct ? 'text-emerald-700' : 'text-rose-700' }}">
                                                    {{ $myRound->is_correct ? '+' . $myRound->points_earned : '0' }}π
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-sm text-slate-500 italic">--</span>
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
                                        <div class="text-xs font-medium text-slate-700 leading-relaxed">Η απάντηση σου: <span class="font-bold">{{ $myAnswer }}</span></div>
                                    @endif
                                </div>

                                <div class="p-4 {{ $opponentRound && $opponentRound->is_correct ? 'bg-emerald-50' : ($opponentRound ? 'bg-rose-50' : 'bg-slate-50') }}">
                                    <div class="flex items-start justify-between mb-3">
                                        <span class="text-sm font-bold text-slate-900">{{ $opponent->display_name }}</span>
                                        @if($opponentRound)
                                            <div class="flex items-center gap-2">
                                                <span class="text-2xl">{{ $opponentRound->is_correct ? '✓' : '×' }}</span>
                                                <span class="text-sm font-bold {{ $opponentRound->is_correct ? 'text-emerald-700' : 'text-rose-700' }}">
                                                    {{ $opponentRound->is_correct ? '+' . $opponentRound->points_earned : '0' }}π
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-sm text-slate-500 italic">--</span>
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
                                        <div class="text-xs font-medium text-slate-700 leading-relaxed">Απάντηση Αντιπάλου: <span class="font-bold">{{ $opponentAnswer }}</span></div>
                                    @endif
                                </div>
                            </div>

                            @if($correctAnswer && (($myRound && !$myRound->is_correct) || ($opponentRound && !$opponentRound->is_correct)))
                                <div class="bg-gradient-to-r from-emerald-50 to-green-50 p-3 border-t border-emerald-200">
                                    <div class="flex items-center gap-2 text-sm">
                                        <span class="font-bold text-emerald-800">Σωστή:</span>
                                        <span class="font-semibold text-emerald-900">{{ $correctAnswer }}</span>
                                    </div>
                                </div>
                            @endif

                            <div class="bg-slate-50 px-4 py-2.5 border-t border-slate-200 flex items-center justify-between">
                                @if($round->question->source_url)
                                    <a href="{{ $round->question->source_url }}" target="_blank" class="text-xs text-indigo-600 hover:text-indigo-800 hover:underline flex items-center gap-1.5 font-medium">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                        </svg>
                                        Επαλήθευση Πηγής
                                    </a>
                                @else
                                    <span></span>
                                @endif
                                <button onclick="reportQuestion({{ $round->question->id }})" class="text-xs text-rose-600 hover:text-rose-800 hover:underline flex items-center gap-1.5 font-medium">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                    Αναφορά Ερώτησης
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>
    </div>

    <div id="reportModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black bg-opacity-50">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">
            <h3 class="text-xl font-bold text-slate-900 mb-4">Αναφορά Ερώτησης</h3>
            <form id="reportForm" method="POST">
                @csrf
                <input type="hidden" name="question_id" id="reportQuestionId">
                <div class="mb-4">
                    <label for="reportReason" class="block text-sm font-medium text-slate-700 mb-2">Αιτιολογία για την αναφορά:</label>
                    <textarea
                        name="reason"
                        id="reportReason"
                        rows="4"
                        required
                        class="w-full rounded-lg border-2 border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-sm p-3 transition-all duration-200"
                        placeholder="Παρακαλώ περιγράψτε το πρόβλημα με αυτήν την ερώτηση..."></textarea>
                </div>
                <div class="flex gap-3">
                    <button
                        type="button"
                        onclick="closeReportModal()"
                        class="flex-1 bg-slate-200 hover:bg-slate-300 text-slate-800 font-medium py-2.5 px-4 rounded-lg text-sm transition-all duration-200">
                        Ακύρωση
                    </button>
                    <button
                        type="submit"
                        class="flex-1 bg-gradient-to-r from-rose-500 to-pink-500 hover:from-rose-600 hover:to-pink-600 text-white font-medium py-2.5 px-4 rounded-lg text-sm transition-all duration-200 shadow-sm hover:shadow">
                        Υποβολή Αναφοράς
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function reportQuestion(questionId) {
            document.getElementById('reportQuestionId').value = questionId;
            document.getElementById('reportModal').classList.remove('hidden');
            document.getElementById('reportModal').classList.add('flex');
        }

        function closeReportModal() {
            document.getElementById('reportModal').classList.add('hidden');
            document.getElementById('reportModal').classList.remove('flex');
            document.getElementById('reportForm').reset();
        }

        document.getElementById('reportForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const questionId = formData.get('question_id');

            try {
                const response = await fetch(`/questions/${questionId}/report`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        question_id: questionId,
                        reason: formData.get('reason')
                    })
                });

                const data = await response.json();

                if (data.success) {
                    alert('Question reported successfully. Thank you for your feedback!');
                    closeReportModal();
                } else {
                    alert(data.message || 'Failed to report question. Please try again.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            }
        });
    </script>
</x-app-layout>
