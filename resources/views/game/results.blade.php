<x-app-layout>
    <div class="py-6 px-4">

        <div class="max-w-5xl mx-auto">
            @php
                $opponent = $game->players->firstWhere('id', '!=', $player->id);
                $iWon = $winner->id === $player->id;
            @endphp

            <!-- Winner Announcement - Minimal -->
            <div class="text-center mb-5">
                <div class="text-4xl mb-2">{{ $iWon ? '🏆' : '👏' }}</div>
                <h1 class="text-2xl font-bold text-slate-900 mb-1">
                    {{ $iWon ? 'You Won!' : 'Good Game!' }}
                </h1>
                <p class="text-sm text-slate-600">{{ $iWon ? 'Congratulations!' : $winner->display_name . ' won this round' }}</p>
            </div>

            <!-- Score Comparison - Compact -->
            <div class="grid grid-cols-2 gap-3 mb-5 max-w-xs mx-auto">
                <div class="text-center p-3 rounded-xl {{ $player->id === $winner->id ? 'bg-gradient-to-br from-amber-50 to-green-50 ring-1 ring-green-300' : 'bg-slate-100' }}">
                    <div class="text-xs font-medium text-slate-600 mb-1">You</div>
                    <div class="text-3xl font-bold text-slate-900">{{ $player->score }}</div>
                </div>
                <div class="text-center p-3 rounded-xl {{ $opponent->id === $winner->id ? 'bg-gradient-to-br from-amber-50 to-green-50 ring-1 ring-green-300' : 'bg-slate-100' }}">
                    <div class="text-xs font-medium text-slate-600 mb-1">{{ $opponent->display_name }}</div>
                    <div class="text-3xl font-bold text-slate-900">{{ $opponent->score }}</div>
                </div>
            </div>

            <!-- Play Again Button -->
            <div class="flex justify-center mb-5">
                <a href="{{ route('game.lobby') }}" class="bg-gradient-to-r from-indigo-500 to-purple-500 hover:from-indigo-600 hover:to-purple-600 text-white font-medium py-4 px-6 rounded-lg text-center text-sm transition-all duration-200 shadow-sm hover:shadow">
                    Play Again
                </a>
            </div>

            <!-- Round by Round - Side by Side -->
            @if($game->rounds->count())
            <div class="mb-5">
                <h2 class="text-base font-semibold text-slate-800 mb-3 text-center">Round Breakdown</h2>

                <div class="space-y-3">
                    @php
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

                        <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
                            <!-- Question Header -->
                            <div class="bg-gradient-to-r from-slate-50 to-gray-50 p-2.5 border-b border-slate-200">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-1.5 mb-1.5">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-200 text-slate-700">
                                                Round {{ $roundNumber }}
                                            </span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium {{ $round->difficulty->value === 'easy' ? 'bg-emerald-100 text-emerald-800' : ($round->difficulty->value === 'medium' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                                {{ ucfirst($round->difficulty->value) }}
                                            </span>
                                            <span class="text-xs text-slate-600">{{ $round->category->icon }} {{ $round->category->name }}</span>
                                        </div>
                                        <p class="text-sm text-slate-900 font-medium">{{ $round->question->question_text }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Answers Side by Side -->
                            <div class="grid md:grid-cols-2 divide-x divide-slate-200">
                                <!-- My Answer -->
                                <div class="p-2.5 {{ $myRound && $myRound->is_correct ? 'bg-emerald-50' : ($myRound ? 'bg-rose-50' : 'bg-slate-50') }}">
                                    <div class="flex items-start justify-between mb-2">
                                        <span class="text-xs font-semibold text-slate-900">You</span>
                                        @if($myRound)
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-lg">{{ $myRound->is_correct ? '✓' : '×' }}</span>
                                                <span class="text-xs font-bold {{ $myRound->is_correct ? 'text-emerald-700' : 'text-rose-700' }}">
                                                    {{ $myRound->is_correct ? '+' . $myRound->points_earned : '0' }} pts
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-xs text-slate-500 italic">Skipped</span>
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
                                        <div class="text-[10px] font-medium text-slate-700 mb-0.5">Your answer:</div>
                                        <div class="text-xs text-slate-800 break-words">{{ $myAnswer }}</div>
                                    @endif
                                </div>

                                <!-- Opponent Answer -->
                                <div class="p-2.5 {{ $opponentRound && $opponentRound->is_correct ? 'bg-emerald-50' : ($opponentRound ? 'bg-rose-50' : 'bg-slate-50') }}">
                                    <div class="flex items-start justify-between mb-2">
                                        <span class="text-xs font-semibold text-slate-900">{{ $opponent->display_name }}</span>
                                        @if($opponentRound)
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-lg">{{ $opponentRound->is_correct ? '✓' : '×' }}</span>
                                                <span class="text-xs font-bold {{ $opponentRound->is_correct ? 'text-emerald-700' : 'text-rose-700' }}">
                                                    {{ $opponentRound->is_correct ? '+' . $opponentRound->points_earned : '0' }} pts
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-xs text-slate-500 italic">Skipped</span>
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
                                        <div class="text-[10px] font-medium text-slate-700 mb-0.5">Their answer:</div>
                                        <div class="text-xs text-slate-800 break-words">{{ $opponentAnswer }}</div>
                                    @endif
                                </div>
                            </div>

                            <!-- Correct Answer Footer -->
                            @if($correctAnswer && (($myRound && !$myRound->is_correct) || ($opponentRound && !$opponentRound->is_correct)))
                                <div class="bg-gradient-to-r from-emerald-50 to-green-50 p-2 border-t border-emerald-200">
                                    <div class="flex items-center gap-1.5 text-xs">
                                        <span class="font-semibold text-emerald-800">Correct:</span>
                                        <span class="text-emerald-900">{{ $correctAnswer }}</span>
                                    </div>
                                </div>
                            @endif

                            <!-- Footer with Source Link and Report Button -->
                            <div class="bg-slate-50 px-2.5 py-1.5 border-t border-slate-200 flex items-center justify-between">
                                @if($round->question->source_url)
                                    <a href="{{ $round->question->source_url }}" target="_blank" class="text-[10px] text-indigo-600 hover:text-indigo-800 hover:underline flex items-center gap-1">
                                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                        </svg>
                                        Verify source
                                    </a>
                                @else
                                    <span></span>
                                @endif
                                <button onclick="reportQuestion({{ $round->question->id }})" class="text-[10px] text-rose-600 hover:text-rose-800 hover:underline flex items-center gap-1">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                    Report
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
            <!-- Dashboard Button -->
            @auth
            <div class="flex justify-center">
                <a href="{{ route('dashboard') }}" class="bg-slate-600 hover:bg-slate-700 text-white font-medium py-2.5 px-6 rounded-lg text-center text-sm transition-all duration-200 shadow-sm hover:shadow">
                    Dashboard
                </a>
            </div>
            @endauth
        </div>
    </div>

    <!-- Report Question Modal -->
    <div id="reportModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black bg-opacity-50">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">
            <h3 class="text-xl font-bold text-slate-900 mb-4">Report Question</h3>
            <form id="reportForm" method="POST">
                @csrf
                <input type="hidden" name="question_id" id="reportQuestionId">
                <div class="mb-4">
                    <label for="reportReason" class="block text-sm font-medium text-slate-700 mb-2">Reason for report:</label>
                    <textarea
                        name="reason"
                        id="reportReason"
                        rows="4"
                        required
                        class="w-full rounded-lg border-2 border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-sm p-3 transition-all duration-200"
                        placeholder="Please describe the issue with this question..."></textarea>
                </div>
                <div class="flex gap-3">
                    <button
                        type="button"
                        onclick="closeReportModal()"
                        class="flex-1 bg-slate-200 hover:bg-slate-300 text-slate-800 font-medium py-2.5 px-4 rounded-lg text-sm transition-all duration-200">
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="flex-1 bg-gradient-to-r from-rose-500 to-pink-500 hover:from-rose-600 hover:to-pink-600 text-white font-medium py-2.5 px-4 rounded-lg text-sm transition-all duration-200 shadow-sm hover:shadow">
                        Submit Report
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
