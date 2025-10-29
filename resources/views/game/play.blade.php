<x-app-layout>
    <script>
        window.gameBoard = function(initialGame, player, categories, isMyTurn, usedCombinations, activeRound) {
            return {
                game: initialGame,
                player: player,
                categories: categories,
                players: {},
                opponent: null,
                isMyTurn: isMyTurn,
                phase: 'category', // category, difficulty, question, result
                loading: false,
                currentQuestion: null,
                currentCategory: null,
                availableDifficulties: { easy: true, medium: true, hard: true },
                usedCombinations: usedCombinations || [],
                opponentMove: null,
                opponentTimeRemaining: 60,
                opponentTimer: null,
                inactivityTimeRemaining: 120,
                inactivityTimer: null,
                inactivityTimerReady: false,
                turnStartedAt: null,
                showInactivityWarning: false,
                answer: '',
                topFiveAnswers: ['', '', '', '', ''],
                timeRemaining: 60,
                questionTimerReady: false,
                timer: null,
                lastResult: null,
                pollingInterval: null,

                init() {
                    this.updatePlayers();
                    this.startPolling();

                    // Check if there's an active unanswered question
                    if (activeRound && this.isMyTurn) {
                        this.restoreActiveRound(activeRound);
                    } else if (!this.isMyTurn) {
                        this.phase = 'waiting';
                    }

                    this.startInactivityTimer();
                },

                restoreActiveRound(round) {
                    // Restore the question that must be answered
                    this.currentQuestion = {
                        id: round.question.id,
                        text: round.question.question_text,
                        type: round.question.question_type,
                        difficulty: round.difficulty,
                        answers: round.question.answers || null
                    };
                    this.phase = 'question';

                    // Calculate elapsed time from round start
                    const now = Math.floor(Date.now() / 1000);
                    const roundStartedAt = new Date(round.started_at).getTime() / 1000;
                    const elapsed = now - roundStartedAt;
                    this.timeRemaining = Math.max(0, 60 - elapsed);

                    // Mark timer as ready after calculation
                    this.questionTimerReady = true;

                    // Stop inactivity timer since we're now on a question
                    this.stopInactivityTimer();

                    // Start timer from the calculated remaining time
                    if (this.timeRemaining > 0) {
                        this.startTimer();
                    } else {
                        // Time expired, auto-submit empty answer
                        this.submitAnswer();
                    }
                },

                updatePlayers() {
                    this.players = {};
                    this.game.players.forEach(p => {
                        this.players[p.id] = p;
                        if (p.id !== this.player.id) {
                            this.opponent = p;
                        }
                    });
                },

                async selectCategory(categoryId) {
                    if (!this.isMyTurn || this.loading) return;

                    this.loading = true;
                    try {
                        const response = await fetch(`/game/${this.game.id}/select-category`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ category_id: categoryId })
                        });

                        const data = await response.json();
                        if (data.success) {
                            this.currentCategory = categoryId;
                            this.availableDifficulties = data.available_difficulties;
                            this.phase = 'difficulty';
                            this.showInactivityWarning = false;
                        }
                    } finally {
                        this.loading = false;
                    }
                },

                async selectCategoryAndDifficulty(categoryId, difficulty) {
                    if (!this.isMyTurn || this.loading) return;
                    if (!this.isCategoryDifficultyAvailable(categoryId, difficulty)) return;

                    this.loading = true;
                    this.currentCategory = categoryId;

                    try {
                        // First select category
                        await fetch(`/game/${this.game.id}/select-category`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ category_id: categoryId })
                        });

                        // Then select difficulty
                        const response = await fetch(`/game/${this.game.id}/select-difficulty`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ difficulty: difficulty })
                        });

                        const data = await response.json();
                        if (data.success) {
                            this.currentQuestion = data.question;
                            this.phase = 'question';
                            this.questionTimerReady = true;
                            this.stopInactivityTimer(); // Stop the inactivity timer
                            this.startTimer();
                            this.showInactivityWarning = false;
                        }

                        if(data.error){
                            alert(data.error)
                        }
                    } finally {
                        this.loading = false;
                    }
                },

                async selectDifficulty(difficulty) {
                    if (!this.isMyTurn || this.loading) return;

                    this.loading = true;
                    try {
                        const response = await fetch(`/game/${this.game.id}/select-difficulty`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ difficulty: difficulty })
                        });

                        const data = await response.json();
                        if (data.success) {
                            this.currentQuestion = data.question;
                            this.phase = 'question';
                            this.questionTimerReady = true;
                            this.stopInactivityTimer(); // Stop the inactivity timer
                            this.startTimer();
                            this.showInactivityWarning = false;
                        }
                    } finally {
                        this.loading = false;
                    }
                },

                async submitAnswer() {
                    if (this.loading) return;

                    // Allow submission even if not valid (for timeout scenarios)
                    const forceSubmit = !this.canSubmit;

                    this.stopTimer();
                    this.loading = true;

                    try {
                        let answerData = this.answer;
                        if (this.currentQuestion?.type === 'top_5') {
                            answerData = this.topFiveAnswers.filter(a => a.trim() !== '');
                        }

                        const response = await fetch(`/game/${this.game.id}/submit-answer`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ answer: answerData })
                        });

                        const data = await response.json();
                        this.lastResult = data;
                        this.phase = 'result';

                        setTimeout(() => {
                            if (data.game_status === 'completed') {
                                window.location.href = `/game/${this.game.id}/results`;
                            } else {
                                this.resetForNextRound();
                            }
                        }, 4000);
                    } finally {
                        this.loading = false;
                    }
                },

                startTimer() {
                    // Only reset to 60 if timeRemaining is already at 60 (new question)
                    // Otherwise keep the restored value (from page refresh)
                    if (this.timeRemaining === 60 || this.timeRemaining > 60) {
                        this.timeRemaining = 60;
                    }

                    this.timer = setInterval(() => {
                        this.timeRemaining--;
                        if (this.timeRemaining <= 0) {
                            this.submitAnswer();
                        }
                    }, 1000);
                },

                stopTimer() {
                    if (this.timer) {
                        clearInterval(this.timer);
                        this.timer = null;
                    }
                },

                startOpponentTimer(startedAt) {
                    this.stopOpponentTimer();

                    const updateOpponentTime = () => {
                        const now = Math.floor(Date.now() / 1000);
                        const elapsed = now - startedAt;
                        this.opponentTimeRemaining = Math.max(0, 60 - elapsed);

                        if (this.opponentTimeRemaining <= 0) {
                            this.stopOpponentTimer();
                        }
                    };

                    updateOpponentTime();
                    this.opponentTimer = setInterval(updateOpponentTime, 1000);
                },

                stopOpponentTimer() {
                    if (this.opponentTimer) {
                        clearInterval(this.opponentTimer);
                        this.opponentTimer = null;
                    }
                    this.opponentTimeRemaining = 60;
                },

                startInactivityTimer() {
                    this.stopInactivityTimer();

                    const updateInactivityTime = () => {
                        if (!this.turnStartedAt) return;

                        const now = Math.floor(Date.now() / 1000);
                        const elapsed = now - this.turnStartedAt;
                        this.inactivityTimeRemaining = Math.max(0, 120 - elapsed);

                        // Mark as ready after first calculation
                        if (!this.inactivityTimerReady) {
                            this.inactivityTimerReady = true;
                        }

                        // Show warning when below 60 seconds
                        this.showInactivityWarning = this.inactivityTimeRemaining < 60;

                        if (this.inactivityTimeRemaining <= 0) {
                            this.stopInactivityTimer();
                        }
                    };

                    updateInactivityTime();
                    this.inactivityTimer = setInterval(updateInactivityTime, 1000);
                },

                stopInactivityTimer() {
                    if (this.inactivityTimer) {
                        clearInterval(this.inactivityTimer);
                        this.inactivityTimer = null;
                    }
                    this.inactivityTimeRemaining = 120;
                    this.inactivityTimerReady = false;
                    this.showInactivityWarning = false;
                },

                resetForNextRound() {
                    this.answer = '';
                    this.topFiveAnswers = ['', '', '', '', ''];
                    this.currentQuestion = null;
                    this.currentCategory = null;
                    this.availableDifficulties = { easy: true, medium: true, hard: true };
                    this.lastResult = null;
                    this.questionTimerReady = false;
                    this.phase = 'category';
                    this.isMyTurn = false;

                    // Restart inactivity timer for next turn
                    this.startInactivityTimer();
                },

                async startPolling() {
                    this.pollingInterval = setInterval(async () => {
                        try {
                            const response = await fetch(`/game/${this.game.id}/state`);
                            const data = await response.json();

                            this.game = data;
                            this.usedCombinations = data.used_combinations || [];
                            this.turnStartedAt = data.turn_started_at;

                            if (data.current_move && data.current_move.player_id !== this.player.id) {
                                this.opponentMove = data.current_move;

                                if (data.current_move.phase === 'difficulty' && data.current_move.started_at) {
                                    this.startOpponentTimer(data.current_move.started_at);
                                } else {
                                    this.stopOpponentTimer();
                                }
                            } else {
                                this.opponentMove = null;
                                this.stopOpponentTimer();
                            }

                            this.updatePlayers();
                            this.isMyTurn = data.current_turn_player_id === this.player.id;

                            if (this.isMyTurn && this.phase === 'waiting') {
                                this.phase = 'category';
                            }

                            if (data.status === 'completed') {
                                if (data.auto_forfeit) {
                                    alert('Game ended due to inactivity!');
                                }
                                window.location.href = `/game/${this.game.id}/results`;
                            }
                        } catch (error) {
                            console.error('Polling error:', error);
                        }
                    }, 2000);
                },

                async forfeitGame() {
                    if (!confirm('Are you sure you want to forfeit this game? Your opponent will be declared the winner.')) {
                        return;
                    }

                    this.stopTimer();
                    this.stopOpponentTimer();
                    if (this.pollingInterval) {
                        clearInterval(this.pollingInterval);
                    }

                    try {
                        const response = await fetch(`/game/${this.game.id}/forfeit`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });

                        const data = await response.json();
                        if (data.success) {
                            window.location.href = data.redirect_url;
                        }
                    } catch (error) {
                        console.error('Forfeit error:', error);
                        alert('Failed to forfeit game. Please try again.');
                    }
                },

                get canSubmit() {
                    if (!this.currentQuestion) {
                        return false;
                    }
                    if (this.currentQuestion.type === 'text_input') {
                        return this.answer.trim() !== '';
                    }
                    if (this.currentQuestion.type === 'multiple_choice') {
                        return this.answer !== '';
                    }
                    if (this.currentQuestion.type === 'top_5') {
                        return this.topFiveAnswers.filter(a => a.trim() !== '').length >= 5;
                    }
                    return false;
                },

                isCategoryUsed(categoryId) {
                    const used = this.usedCombinations.filter(c => c.category_id === categoryId);
                    return used.length >= 3;
                },

                getCategoryUsedCount(categoryId) {
                    return this.usedCombinations.filter(c => c.category_id === categoryId).length;
                },

                isCategoryDifficultyAvailable(categoryId, difficulty) {
                    return !this.usedCombinations.some(c =>
                        c.category_id === categoryId && c.difficulty === difficulty
                    );
                },

                getDifficultyLabel(difficulty) {
                    const labels = {
                        'easy': 'Easy (1pt)',
                        'medium': 'Medium (2pts)',
                        'hard': 'Hard (3pts)'
                    };
                    return labels[difficulty] || difficulty;
                }
            }
        }
    </script>

    <div class="py-6" x-data="gameBoard(@js($game), @js($player), @js($categories), {{ $isPlayerTurn ? 'true' : 'false' }}, @js($usedCombinations), @js($activeRound))">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-4 lg:gap-6">
                <!-- Left Sidebar - Score and Turn Info -->
                <div class="w-full lg:w-80 flex-shrink-0 space-y-3 lg:space-y-4">
                    <!-- Mobile: Horizontal Score Display -->
                    <div class="lg:hidden bg-white rounded-xl shadow-lg p-4 border border-gray-100">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="p-3 rounded-lg transition-all duration-300"
                                 :class="game.current_turn_player_id === {{ $player->id }} ? 'bg-gradient-to-br from-green-50 to-emerald-50 ring-2 ring-green-400' : 'bg-gray-50'">
                                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">You</div>
                                <div class="text-2xl font-bold bg-gradient-to-r from-green-600 to-emerald-600 bg-clip-text text-transparent" x-text="players[{{ $player->id }}]?.score || 0"></div>
                            </div>
                            <div class="p-3 rounded-lg transition-all duration-300"
                                 :class="game.current_turn_player_id !== {{ $player->id }} ? 'bg-gradient-to-br from-blue-50 to-indigo-50 ring-2 ring-blue-400' : 'bg-gray-50'" x-show="opponent">
                                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Opponent</div>
                                <div class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent" x-text="opponent?.score || 0"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Inactivity Timer -->
                    <div x-show="phase !== 'question' && opponentMove?.phase !== 'difficulty'"
                         class="rounded-xl p-3 lg:p-4 shadow-lg transition-all duration-300"
                         :class="[
                             isMyTurn ? (showInactivityWarning ? 'bg-gradient-to-r from-red-50 to-pink-50 border-2 border-red-400' : 'bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-300') : (showInactivityWarning ? 'bg-gradient-to-r from-amber-50 to-yellow-50 border-2 border-yellow-400' : 'bg-gradient-to-r from-slate-50 to-gray-50 border-2 border-gray-300'),
                             !inactivityTimerReady ? 'blur-sm' : ''
                         ]">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full animate-pulse"
                                     :class="showInactivityWarning ? 'bg-red-500' : 'bg-blue-500'"></div>
                                <span class="font-semibold text-gray-800 text-xs lg:text-sm">Time Left</span>
                            </div>
                            <div class="text-xl lg:text-2xl font-bold tracking-tight"
                                 :class="showInactivityWarning ? 'text-red-600' : 'text-blue-600'"
                                 x-text="Math.floor(inactivityTimeRemaining / 60) + ':' + String(inactivityTimeRemaining % 60).padStart(2, '0')">
                            </div>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                            <div class="h-2 rounded-full transition-all duration-1000 shadow-sm"
                                 :class="inactivityTimeRemaining > 60 ? 'bg-gradient-to-r from-blue-400 to-blue-600' : (inactivityTimeRemaining > 30 ? 'bg-gradient-to-r from-yellow-400 to-amber-500' : 'bg-gradient-to-r from-red-400 to-red-600')"
                                 :style="`width: ${(inactivityTimeRemaining / 120) * 100}%`"></div>
                        </div>
                        <div x-show="showInactivityWarning" class="mt-2 text-xs font-medium px-2 py-1 rounded"
                             :class="isMyTurn ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'">
                            <span x-show="isMyTurn">Time is running out!</span>
                            <span x-show="!isMyTurn">Opponent's time running out</span>
                        </div>
                    </div>

                    <!-- Turn Indicator -->
                    <div class="bg-white rounded-xl shadow-lg p-3 lg:p-4 border border-gray-100">
                        <div x-show="isMyTurn" class="flex items-center justify-center gap-2">
                            <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                            <span class="text-green-600 font-bold text-sm lg:text-base">Your Turn</span>
                        </div>
                        <div x-show="!isMyTurn" class="flex items-center justify-center gap-2">
                            <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                            <span class="text-blue-600 font-bold text-sm lg:text-base">Opponent's Turn</span>
                        </div>
                        <div class="text-center mt-2 text-xs lg:text-sm text-gray-600">
                            Round <span class="font-bold" x-text="game.current_round"></span>/<span x-text="game.max_rounds"></span>
                        </div>
                    </div>

                    <!-- Desktop: Vertical Score Display -->
                    <div class="hidden lg:block bg-white rounded-xl shadow-lg p-5 border border-gray-100">
                        <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-4">Scores</h3>
                        <div class="space-y-4">
                            <div class="p-4 rounded-xl transition-all duration-300"
                                 :class="game.current_turn_player_id === {{ $player->id }} ? 'bg-gradient-to-br from-green-50 to-emerald-50 ring-2 ring-green-400' : 'bg-gray-50'">
                                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">You</div>
                                <div class="text-3xl font-bold bg-gradient-to-r from-green-600 to-emerald-600 bg-clip-text text-transparent" x-text="players[{{ $player->id }}]?.score || 0"></div>
                                <div class="text-xs text-gray-600 mt-1 truncate" x-text="players[{{ $player->id }}]?.display_name"></div>
                            </div>
                            <div class="p-4 rounded-xl transition-all duration-300"
                                 :class="game.current_turn_player_id !== {{ $player->id }} ? 'bg-gradient-to-br from-blue-50 to-indigo-50 ring-2 ring-blue-400' : 'bg-gray-50'" x-show="opponent">
                                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Opponent</div>
                                <div class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent" x-text="opponent?.score || 0"></div>
                                <div class="text-xs text-gray-600 mt-1 truncate" x-text="opponent?.display_name"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Forfeit Button -->
                    <button @click="forfeitGame"
                            class="w-full inline-flex items-center justify-center gap-2 bg-white border-2 border-red-300 hover:border-red-500 text-red-600 hover:text-red-700 font-semibold py-2.5 px-5 rounded-xl shadow-sm hover:shadow-md transition-all duration-200">
                        <span>Forfeit Game</span>
                    </button>


                </div>

                <!-- Main Content Area -->
                <div class="flex-1 min-w-0">
            <!-- Opponent Move Display -->
            <div x-show="opponentMove && !isMyTurn" x-cloak class="bg-gradient-to-r from-indigo-50 to-purple-50 rounded-xl shadow-lg p-6 mb-4 border-2 border-indigo-200">
                <h3 class="text-lg font-bold text-indigo-900 mb-4 flex items-center gap-2">
                    <div class="w-2 h-2 bg-indigo-500 rounded-full animate-pulse"></div>
                    <span x-text="opponent?.display_name"></span>'s Move
                </h3>

                <!-- Category Selected -->
                <div x-show="opponentMove?.phase === 'category'" class="text-center">
                    <div class="inline-flex items-center gap-3 bg-white rounded-xl p-5 shadow-md border border-gray-100">
                        <div class="text-left">
                            <div class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Selected Category</div>
                            <div class="text-xl font-bold text-gray-900 mt-1" x-text="opponentMove?.category?.name"></div>
                        </div>
                    </div>
                </div>

                <!-- Difficulty Selected -->
                <div x-show="opponentMove?.phase === 'difficulty'" class="space-y-4">
                    <!-- Timer -->
                    <div class="bg-white rounded-xl p-4 shadow-md border border-gray-100">
                        <div class="flex justify-between text-sm mb-2">
                            <span class="font-semibold text-gray-700">Time Remaining</span>
                            <span class="font-bold text-indigo-600" x-text="opponentTimeRemaining + 's'"></span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="h-2 rounded-full transition-all duration-1000"
                                 :class="opponentTimeRemaining > 20 ? 'bg-gradient-to-r from-indigo-400 to-indigo-600' : (opponentTimeRemaining > 10 ? 'bg-gradient-to-r from-yellow-400 to-amber-500' : 'bg-gradient-to-r from-red-400 to-red-600')"
                                 :style="`width: ${(opponentTimeRemaining / 60) * 100}%`"></div>
                        </div>
                    </div>

                    <div class="flex items-center justify-center gap-4 flex-wrap">
                        <div class="inline-flex items-center gap-3 bg-white rounded-xl p-4 shadow-md border border-gray-100">
                            <div class="text-left">
                                <div class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Category</div>
                                <div class="font-bold text-gray-900 mt-1" x-text="opponentMove?.category?.name"></div>
                            </div>
                        </div>
                        <div class="text-2xl text-gray-300">→</div>
                        <div class="inline-flex items-center gap-3 bg-white rounded-xl p-4 shadow-md border border-gray-100">
                            <div class="text-left">
                                <div class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Difficulty</div>
                                <div class="font-bold text-gray-900 mt-1" x-text="getDifficultyLabel(opponentMove?.difficulty)"></div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-4 shadow-md mt-3 border border-gray-100">
                        <div class="text-xs text-gray-500 uppercase tracking-wide font-semibold mb-2">Question</div>
                        <div class="text-gray-900 font-medium" x-text="opponentMove?.question"></div>
                    </div>
                    <div class="text-center text-sm text-indigo-700 font-medium">
                        <div class="inline-flex items-center gap-2">
                            <div class="w-2 h-2 bg-indigo-500 rounded-full animate-pulse"></div>
                            Opponent is answering
                        </div>
                    </div>
                </div>

                <!-- Answer Result -->
                <div x-show="opponentMove?.phase === 'result'" class="text-center space-y-4">
                    <div class="flex items-center justify-center gap-4 flex-wrap">
                        <div class="inline-flex items-center gap-2 bg-white rounded-xl p-3 shadow-md text-sm border border-gray-100">
                            <span x-text="opponentMove?.category?.name" class="font-semibold"></span>
                        </div>
                        <div class="inline-flex items-center gap-2 bg-white rounded-xl p-3 shadow-md text-sm border border-gray-100">
                            <span x-text="getDifficultyLabel(opponentMove?.difficulty)" class="font-semibold"></span>
                        </div>
                    </div>

                    <div x-show="opponentMove?.is_correct" class="inline-flex flex-col items-center gap-3 bg-gradient-to-br from-green-50 to-emerald-50 border-2 border-green-400 rounded-xl p-8">
                        <div class="text-6xl font-black text-green-600">✓</div>
                        <div class="text-green-800 font-bold text-xl">Correct Answer</div>
                        <div class="text-green-700 font-semibold">+<span x-text="opponentMove?.points_earned"></span> points</div>
                    </div>

                    <div x-show="!opponentMove?.is_correct" class="inline-flex flex-col items-center gap-3 bg-gradient-to-br from-red-50 to-pink-50 border-2 border-red-400 rounded-xl p-8">
                        <div class="text-6xl font-black text-red-600">×</div>
                        <div class="text-red-800 font-bold text-xl">Incorrect Answer</div>
                        <div class="text-red-700 font-semibold">0 points</div>
                    </div>

                    <!-- Question Text -->
                    <div class="bg-gradient-to-r from-gray-50 to-slate-50 rounded-xl p-4 border border-gray-200">
                        <div class="text-xs text-gray-500 uppercase tracking-wide font-semibold mb-2">Question</div>
                        <div class="text-gray-900 font-medium" x-text="opponentMove?.question"></div>
                    </div>

                    <!-- Answer Details -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-white rounded-xl p-4 shadow-md border border-gray-200">
                            <div class="text-xs text-gray-500 uppercase tracking-wide font-semibold mb-2">Opponent's Answer</div>
                            <div class="text-gray-900 font-bold" x-text="opponentMove?.answer"></div>
                        </div>
                        <div class="bg-white rounded-xl p-4 shadow-md border border-gray-200">
                            <div class="text-xs text-gray-500 uppercase tracking-wide font-semibold mb-2">Correct Answer</div>
                            <div class="text-green-700 font-bold" x-text="opponentMove?.correct_answer"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Game Phase Display -->
            <div class="bg-white rounded-xl shadow-lg p-3 lg:p-4 border border-gray-100">
                <!-- Phase 1 & 2: Combined Category & Difficulty Selection Table -->
                <!-- Show to current player OR waiting player (to see the board) -->
                <div x-show="phase === 'category' || phase === 'difficulty' || phase === 'waiting'" x-cloak>
                    <h3 class="text-lg lg:text-2xl font-bold text-gray-900 mb-4 lg:mb-6">Select Category & Difficulty</h3>

                    <!-- Mobile: Stacked Cards View -->
                    <div class="lg:hidden space-y-3">
                        <template x-for="category in categories" :key="category.id">
                            <div class="bg-gradient-to-r from-gray-50 to-slate-50 rounded-xl p-4 border border-gray-200">
                                <div class="flex items-center gap-3 mb-3 pb-3 border-b border-gray-200">
                                    <div class="text-2xl" x-text="category.icon"></div>
                                    <span class="font-semibold text-gray-900" x-text="category.name"></span>
                                </div>
                                <div class="space-y-2">
                                    <!-- Easy -->
                                    <button @click="selectCategoryAndDifficulty(category.id, 'easy')"
                                            :disabled="!isMyTurn || loading || !isCategoryDifficultyAvailable(category.id, 'easy')"
                                            :class="isCategoryDifficultyAvailable(category.id, 'easy') ? 'bg-gradient-to-r from-green-400 to-emerald-500 hover:from-green-500 hover:to-emerald-600 text-white shadow-md' : 'bg-gray-100 text-gray-400 cursor-not-allowed'"
                                            class="w-full py-2.5 px-4 rounded-lg font-semibold transition-all duration-200 disabled:cursor-not-allowed text-sm">
                                        <span x-show="isCategoryDifficultyAvailable(category.id, 'easy')">Easy (1pt)</span>
                                        <span x-show="!isCategoryDifficultyAvailable(category.id, 'easy')">Easy - Used</span>
                                    </button>
                                    <!-- Medium -->
                                    <button @click="selectCategoryAndDifficulty(category.id, 'medium')"
                                            :disabled="!isMyTurn || loading || !isCategoryDifficultyAvailable(category.id, 'medium')"
                                            :class="isCategoryDifficultyAvailable(category.id, 'medium') ? 'bg-gradient-to-r from-yellow-400 to-amber-500 hover:from-yellow-500 hover:to-amber-600 text-white shadow-md' : 'bg-gray-100 text-gray-400 cursor-not-allowed'"
                                            class="w-full py-2.5 px-4 rounded-lg font-semibold transition-all duration-200 disabled:cursor-not-allowed text-sm">
                                        <span x-show="isCategoryDifficultyAvailable(category.id, 'medium')">Medium (2pts)</span>
                                        <span x-show="!isCategoryDifficultyAvailable(category.id, 'medium')">Medium - Used</span>
                                    </button>
                                    <!-- Hard -->
                                    <button @click="selectCategoryAndDifficulty(category.id, 'hard')"
                                            :disabled="!isMyTurn || loading || !isCategoryDifficultyAvailable(category.id, 'hard')"
                                            :class="isCategoryDifficultyAvailable(category.id, 'hard') ? 'bg-gradient-to-r from-red-400 to-rose-500 hover:from-red-500 hover:to-rose-600 text-white shadow-md' : 'bg-gray-100 text-gray-400 cursor-not-allowed'"
                                            class="w-full py-2.5 px-4 rounded-lg font-semibold transition-all duration-200 disabled:cursor-not-allowed text-sm">
                                        <span x-show="isCategoryDifficultyAvailable(category.id, 'hard')">Hard (3pts)</span>
                                        <span x-show="!isCategoryDifficultyAvailable(category.id, 'hard')">Hard - Used</span>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Desktop: Table View -->
                    <div class="hidden lg:block overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="bg-gradient-to-r from-gray-50 to-slate-50">
                                    <th class="border border-gray-200 px-6 py-4 text-left font-bold text-gray-700">Category</th>
                                    <th class="border border-gray-200 px-6 py-4 text-center font-bold text-green-700">Easy<br/><span class="text-xs font-normal text-gray-500">1 point</span></th>
                                    <th class="border border-gray-200 px-6 py-4 text-center font-bold text-yellow-700">Medium<br/><span class="text-xs font-normal text-gray-500">2 points</span></th>
                                    <th class="border border-gray-200 px-6 py-4 text-center font-bold text-red-700">Hard<br/><span class="text-xs font-normal text-gray-500">3 points</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="category in categories" :key="category.id">
                                    <tr class="hover:bg-gradient-to-r hover:from-blue-50/30 hover:to-indigo-50/30 transition-all duration-200">
                                        <td class="border border-gray-200 px-3 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="text-2xl" x-text="category.icon"></div>
                                                <span class="font-semibold text-gray-900" x-text="category.name"></span>
                                            </div>
                                        </td>

                                        <!-- Easy Button -->
                                        <td class="border border-gray-200 px-4 py-3">
                                            <button @click="selectCategoryAndDifficulty(category.id, 'easy')"
                                                    :disabled="!isMyTurn || loading || !isCategoryDifficultyAvailable(category.id, 'easy')"
                                                    :class="isCategoryDifficultyAvailable(category.id, 'easy') ? 'bg-gradient-to-r from-green-400 to-emerald-500 hover:from-green-500 hover:to-emerald-600 text-white shadow-md hover:shadow-lg scale-100 hover:scale-105' : 'bg-gray-100 text-gray-400 cursor-not-allowed'"
                                                    class="w-full py-3 px-4 rounded-lg font-semibold transition-all duration-200 disabled:cursor-not-allowed">
                                                <span x-show="isCategoryDifficultyAvailable(category.id, 'easy')">Select</span>
                                                <span x-show="!isCategoryDifficultyAvailable(category.id, 'easy')">Used</span>
                                            </button>
                                        </td>

                                        <!-- Medium Button -->
                                        <td class="border border-gray-200 px-4 py-3">
                                            <button @click="selectCategoryAndDifficulty(category.id, 'medium')"
                                                    :disabled="!isMyTurn || loading || !isCategoryDifficultyAvailable(category.id, 'medium')"
                                                    :class="isCategoryDifficultyAvailable(category.id, 'medium') ? 'bg-gradient-to-r from-yellow-400 to-amber-500 hover:from-yellow-500 hover:to-amber-600 text-white shadow-md hover:shadow-lg scale-100 hover:scale-105' : 'bg-gray-100 text-gray-400 cursor-not-allowed'"
                                                    class="w-full py-3 px-4 rounded-lg font-semibold transition-all duration-200 disabled:cursor-not-allowed">
                                                <span x-show="isCategoryDifficultyAvailable(category.id, 'medium')">Select</span>
                                                <span x-show="!isCategoryDifficultyAvailable(category.id, 'medium')">Used</span>
                                            </button>
                                        </td>

                                        <!-- Hard Button -->
                                        <td class="border border-gray-200 px-4 py-3">
                                            <button @click="selectCategoryAndDifficulty(category.id, 'hard')"
                                                    :disabled="!isMyTurn || loading || !isCategoryDifficultyAvailable(category.id, 'hard')"
                                                    :class="isCategoryDifficultyAvailable(category.id, 'hard') ? 'bg-gradient-to-r from-red-400 to-rose-500 hover:from-red-500 hover:to-rose-600 text-white shadow-md hover:shadow-lg scale-100 hover:scale-105' : 'bg-gray-100 text-gray-400 cursor-not-allowed'"
                                                    class="w-full py-3 px-4 rounded-lg font-semibold transition-all duration-200 disabled:cursor-not-allowed">
                                                <span x-show="isCategoryDifficultyAvailable(category.id, 'hard')">Select</span>
                                                <span x-show="!isCategoryDifficultyAvailable(category.id, 'hard')">Used</span>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Phase 3: Question & Answer -->
                <div x-show="phase === 'question' && currentQuestion" x-cloak>
                    <div class="mb-6">
                        <!-- Timer -->
                        <div class="mb-6 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-4 border border-blue-200 transition-all duration-300"
                             :class="!questionTimerReady ? 'blur-sm' : ''">
                            <div class="flex justify-between text-sm mb-2">
                                <span class="font-semibold text-gray-700">Time Remaining</span>
                                <span class="font-bold text-blue-600 text-lg" x-text="timeRemaining + 's'"></span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="h-3 rounded-full transition-all duration-1000"
                                     :class="timeRemaining > 30 ? 'bg-gradient-to-r from-blue-400 to-blue-600' : (timeRemaining > 10 ? 'bg-gradient-to-r from-yellow-400 to-amber-500' : 'bg-gradient-to-r from-red-400 to-red-600')"
                                     :style="`width: ${(timeRemaining / 60) * 100}%`"></div>
                            </div>
                        </div>

                        <h3 class="text-2xl font-bold text-gray-900 mb-6 leading-relaxed" x-text="currentQuestion?.text"></h3>

                        <!-- Text Input Answer -->
                        <div x-show="currentQuestion?.type === 'text_input'" class="space-y-4">
                            <input type="text" x-model="answer"
                                   @keydown.enter="submitAnswer"
                                   class="w-full rounded-xl border-2 border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 text-lg p-4 transition-all duration-200"
                                   placeholder="Type your answer...">
                        </div>

                        <!-- Multiple Choice Answer -->
                        <div x-show="currentQuestion?.type === 'multiple_choice'" class="space-y-3">
                            <template x-for="(ans, index) in currentQuestion?.answers" :key="ans.id">
                                <button @click="answer = ans.id"
                                        :class="answer === ans.id ? 'border-blue-500 bg-gradient-to-r from-blue-50 to-indigo-50 ring-2 ring-blue-400 scale-105' : 'border-gray-300 hover:border-blue-300 hover:bg-blue-50/30'"
                                        class="w-full p-5 border-2 rounded-xl transition-all duration-200 text-left shadow-sm hover:shadow-md">
                                    <span class="font-bold text-blue-600 mr-3" x-text="String.fromCharCode(65 + index)"></span>
                                    <span class="text-gray-900 font-medium" x-text="ans.text"></span>
                                </button>
                            </template>
                        </div>

                        <!-- Top 5 Answer -->
                        <div x-show="currentQuestion?.type === 'top_5'" class="space-y-3">
                            <p class="text-sm text-gray-600 font-medium mb-4">Enter 5 correct answers:</p>
                            <template x-for="i in [0,1,2,3,4]" :key="i">
                                <div class="flex items-center gap-3">
                                    <span class="font-bold text-gray-600" x-text="(i + 1)"></span>
                                    <input type="text" x-model="topFiveAnswers[i]"
                                           class="flex-1 rounded-xl border-2 border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 p-3 transition-all duration-200"
                                           :placeholder="'Answer ' + (i + 1)">
                                </div>
                            </template>
                        </div>
                    </div>

                    <button @click="submitAnswer"
                            :disabled="!canSubmit || loading"
                            class="w-full bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-bold py-4 px-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none transform hover:scale-105">
                        Submit Answer
                    </button>
                </div>

                <!-- Phase 4: Result Display -->
                <div x-show="phase === 'result' && lastResult" x-cloak>
                    <div class="text-center py-8 space-y-6">
                        <div x-show="lastResult?.is_correct" class="animate-in fade-in duration-500">
                            <div class="text-8xl font-black mb-4 text-green-600">✓</div>
                            <div class="text-3xl font-bold mb-3 text-green-800">Correct!</div>
                            <div class="text-xl font-semibold text-green-700">+<span x-text="lastResult?.points_earned"></span> points</div>
                        </div>
                        <div x-show="!lastResult?.is_correct" class="animate-in fade-in duration-500">
                            <div class="text-8xl font-black mb-4 text-red-600">×</div>
                            <div class="text-3xl font-bold mb-3 text-red-800">Incorrect</div>
                            <div class="text-xl font-semibold text-red-700">0 points</div>
                        </div>

                        <!-- Question Text -->
                        <div class="bg-gradient-to-r from-gray-50 to-slate-50 rounded-xl p-4 border border-gray-200">
                            <div class="text-xs text-gray-500 uppercase tracking-wide font-semibold mb-2">Question</div>
                            <div class="text-gray-900 font-medium" x-text="lastResult?.question_text"></div>
                        </div>

                        <!-- Answer Details -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-2xl mx-auto">
                            <div class="bg-white rounded-xl p-4 shadow-md border border-gray-200">
                                <div class="text-xs text-gray-500 uppercase tracking-wide font-semibold mb-2">Your Answer</div>
                                <div class="text-gray-900 font-bold" x-text="lastResult?.player_answer"></div>
                            </div>
                            <div class="bg-white rounded-xl p-4 shadow-md border border-gray-200">
                                <div class="text-xs text-gray-500 uppercase tracking-wide font-semibold mb-2">Correct Answer</div>
                                <div class="text-green-700 font-bold" x-text="lastResult?.correct_answer"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Loading State -->
                <div x-show="loading" class="text-center py-12">
                    <div class="inline-flex items-center gap-3">
                        <div class="w-4 h-4 bg-blue-600 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                        <div class="w-4 h-4 bg-indigo-600 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                        <div class="w-4 h-4 bg-purple-600 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                    </div>
                    <p class="mt-4 text-gray-600 font-medium">Processing...</p>
                </div>
            </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
