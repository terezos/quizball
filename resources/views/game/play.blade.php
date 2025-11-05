<x-app-layout>
    <x-slot name="title">QuizBall - Παιχνίδι σε Εξέλιξη</x-slot>
    <style>
        /* Disable text selection */
        .no-select {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            -webkit-touch-callout: none;
        }
    </style>

    {{--    <script>--}}
    {{--        // Disable copy, paste, cut, and right-click--}}
    {{--        document.addEventListener('DOMContentLoaded', function() {--}}
    {{--            const gameArea = document.body;--}}

    {{--            // Prevent copying--}}
    {{--            gameArea.addEventListener('copy', function(e) {--}}
    {{--                e.preventDefault();--}}
    {{--                return false;--}}
    {{--            });--}}

    {{--            // Prevent cutting--}}
    {{--            gameArea.addEventListener('cut', function(e) {--}}
    {{--                e.preventDefault();--}}
    {{--                return false;--}}
    {{--            });--}}

    {{--            // Prevent pasting--}}
    {{--            gameArea.addEventListener('paste', function(e) {--}}
    {{--                e.preventDefault();--}}
    {{--                return false;--}}
    {{--            });--}}

    {{--            // Prevent right-click context menu--}}
    {{--            gameArea.addEventListener('contextmenu', function(e) {--}}
    {{--                e.preventDefault();--}}
    {{--                return false;--}}
    {{--            });--}}

    {{--            // Prevent keyboard shortcuts for copy/paste--}}
    {{--            gameArea.addEventListener('keydown', function(e) {--}}
    {{--                // Check for Ctrl+C, Ctrl+X, Ctrl+V, Cmd+C, Cmd+X, Cmd+V--}}
    {{--                if ((e.ctrlKey || e.metaKey) && (e.key === 'c' || e.key === 'x' || e.key === 'v' || e.key === 'C' || e.key === 'X' || e.key === 'V')) {--}}
    {{--                    // Allow in input fields for answers--}}
    {{--                    if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {--}}
    {{--                        // Allow only paste in answer fields, block copy--}}
    {{--                        if (e.key === 'v' || e.key === 'V') {--}}
    {{--                            return true;--}}
    {{--                        }--}}
    {{--                    }--}}
    {{--                    e.preventDefault();--}}
    {{--                    return false;--}}
    {{--                }--}}
    {{--            });--}}

    {{--            // Prevent drag and drop--}}
    {{--            gameArea.addEventListener('dragstart', function(e) {--}}
    {{--                e.preventDefault();--}}
    {{--                return false;--}}
    {{--            });--}}
    {{--        });--}}
    {{--    </script>--}}

    <script>
        window.gameBoard = function(initialGame, player, categories, isMyTurn, usedCombinations, activeRound, turnStartedAt) {
            return {
                game: initialGame,
                player: player,
                categories: categories,
                players: {},
                opponent: null,
                isMyTurn: isMyTurn,
                phase: 'category',
                loading: false,
                currentQuestion: null,
                currentCategory: null,
                availableDifficulties: { easy: true, medium: true, hard: true },
                usedCombinations: usedCombinations || [],
                opponentMove: null,
                echoChannel: null,
                opponentTimeRemaining: 60,
                opponentTimer: null,
                opponentInactivityTimeRemaining: 120,
                opponentInactivityTimer: null,
                inactivityTimeRemaining: 120,
                inactivityTimer: null,
                inactivityTimerReady: false,
                turnStartedAt: turnStartedAt,
                showInactivityWarning: false,
                answer: '',
                topFiveAnswers: ['', '', '', '', ''],
                timeRemaining: 60,
                questionTimerReady: false,
                timer: null,
                lastResult: null,
                tabSwitchCount: 0,
                alreadyForfeit: false,
                showTabSwitchWarning: false,
                showForfeitConfirmation: false,
                showMoveHistory: false,
                consecutiveTimeouts: 0,
                tabSwitchListenerAdded: false,
                isPageUnloading: false,

                init() {
                    this.updatePlayers();
                    this.setupEcho();
                    this.loadGameHistory();

                    const savedTabSwitchCount = localStorage.getItem(`game_${this.game.id}_tabSwitchCount`);
                    if (savedTabSwitchCount !== null) {
                        this.tabSwitchCount = parseInt(savedTabSwitchCount, 10);
                    }

                    const savedTimeouts = localStorage.getItem(`game_${this.game.id}_consecutiveTimeouts`);
                    if (savedTimeouts !== null) {
                        this.consecutiveTimeouts = parseInt(savedTimeouts, 10);
                    }

                    if (activeRound && this.isMyTurn) {
                        this.restoreActiveRound(activeRound);
                    } else if (!this.isMyTurn) {
                        this.phase = 'waiting';
                        if (this.turnStartedAt) {
                            this.startOpponentInactivityTimer(this.turnStartedAt);
                        }
                    } else if (this.isMyTurn && this.phase === 'category') {
                        this.startInactivityTimer();
                    }

                    this.setupTabSwitchDetection();
                },

                setupTabSwitchDetection() {
                    if (this.tabSwitchListenerAdded) {
                        return;
                    }

                    this.tabSwitchListenerAdded = true;

                    window.addEventListener('beforeunload', () => {
                        this.isPageUnloading = true;
                    });

                    document.addEventListener('visibilitychange', () => {
                        if (this.isPageUnloading) {
                            return;
                        }

                        if (document.hidden && this.phase === 'question' && this.isMyTurn) {
                            this.tabSwitchCount++;

                            localStorage.setItem(`game_${this.game.id}_tabSwitchCount`, this.tabSwitchCount);

                            if (this.tabSwitchCount >= 2 && !this.alreadyForfeit) {
                                this.showTabSwitchWarning = true;
                                setTimeout(() => {
                                    this.forfeitGame(true);
                                }, 2000);
                            } else {
                                this.showTabSwitchWarning = true;
                            }
                        }
                    });
                },

                closeTabWarning() {
                    this.showTabSwitchWarning = false;
                },

                restoreActiveRound(round) {
                    this.currentQuestion = {
                        id: round.question.id,
                        text: round.question.question_text,
                        type: round.question.question_type,
                        created_by: round.question.creator.name,
                        difficulty: round.difficulty,
                        answers: round.question.answers || null
                    };

                    this.phase = 'question';

                    const now = Math.floor(Date.now() / 1000);
                    const roundStartedAt = new Date(round.started_at).getTime() / 1000;
                    const elapsed = now - roundStartedAt;
                    this.timeRemaining = Math.max(0, 60 - elapsed);

                    this.questionTimerReady = true;

                    this.stopInactivityTimer();

                    if (this.timeRemaining > 0) {
                        this.startTimer();
                    } else {
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

                async loadGameHistory() {
                    try {
                        const response = await fetch(`/game/${this.game.id}/rounds`, {
                            headers: {
                                'Accept': 'application/json',
                            }
                        });

                        if (!response.ok) {
                            throw new Error('Failed to load game history');
                        }

                        const data = await response.json();
                        this.game.rounds = data.rounds || [];
                    } catch (error) {
                        console.error('Failed to load game history:', error);
                        this.game.rounds = [];
                    }
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
                            this.stopInactivityTimer();
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
                    this.freezeTime();

                    try {
                        await fetch(`/game/${this.game.id}/select-category`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ category_id: categoryId })
                        });

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
                            this.startTimer();
                            this.showInactivityWarning = false;
                        }
                    } finally {
                        this.loading = false;
                    }
                },

                async passQuestion() {
                    if (this.loading) return;

                    this.stopTimer();
                    this.loading = true;

                    try {
                        const response = await fetch(`/game/${this.game.id}/submit-answer`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                answer: '',
                                autofault: true
                            })
                        });

                        if (!response.ok) {
                            console.error('Pass question failed:', response.status);
                            alert('Failed to pass question. Please try again.');
                            return;
                        }

                        const data = await response.json();
                        this.lastResult = data;
                        this.phase = 'result';

                        this.consecutiveTimeouts = 0;
                        localStorage.setItem(`game_${this.game.id}_consecutiveTimeouts`, 0);

                        setTimeout(() => {
                            if (data.game_status === 'completed') {
                                localStorage.removeItem(`game_${this.game.id}_consecutiveTimeouts`);
                                window.location.href = `/game/${this.game.id}/results`;
                            } else {
                                this.resetForNextRound();
                            }
                        }, 5000);
                    } catch (error) {
                        console.error('Pass question error:', error);
                        alert('An error occurred while passing the question. Please refresh the page.');
                    } finally {
                        this.loading = false;
                    }
                },

                async submitAnswer() {
                    if (this.loading) return;

                    const isTimeout = this.timeRemaining <= 0 || !this.canSubmit;

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

                        if (!response.ok) {
                            console.error('Submit answer failed:', response.status);
                            alert('Failed to submit answer. Please try again.');
                            return;
                        }

                        const data = await response.json();
                        this.lastResult = data;
                        this.phase = 'result';

                        if (isTimeout && !data.is_correct) {
                            this.consecutiveTimeouts++;
                            localStorage.setItem(`game_${this.game.id}_consecutiveTimeouts`, this.consecutiveTimeouts);

                            if (this.consecutiveTimeouts >= 2 && !this.alreadyForfeit) {
                                alert('You have missed 2 consecutive questions due to timeout. The game will be forfeited.');
                                this.freezeTime()
                                this.loading = true;
                                setTimeout(() => {
                                    this.forfeitGame(true);
                                }, 2000);
                                return;
                            }
                        } else {
                            this.consecutiveTimeouts = 0;
                            localStorage.setItem(`game_${this.game.id}_consecutiveTimeouts`, 0);
                        }

                        setTimeout(() => {
                            if (data.game_status === 'completed') {
                                localStorage.removeItem(`game_${this.game.id}_consecutiveTimeouts`);
                                window.location.href = `/game/${this.game.id}/results`;
                            } else {
                                this.resetForNextRound();
                            }
                        }, 5000);
                    } catch (error) {
                        console.error('Submit answer error:', error);
                        alert('An error occurred while submitting your answer. Please refresh the page.');
                    } finally {
                        this.loading = false;
                    }
                },

                startTimer() {
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

                        if (!this.inactivityTimerReady) {
                            this.inactivityTimerReady = true;
                        }

                        this.showInactivityWarning = this.inactivityTimeRemaining < 60;

                        if (this.inactivityTimeRemaining <= 0) {
                            this.freezeTime()
                            alert('Έχετε μείνει ανενεργός για πολύ ώρα. Το παιχνίδι θα κατακυρωθεί υπέρ του αντιπάλου σας.');
                            this.loading = true;
                            this.forfeitGame(true);
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

                freezeTime(){
                    clearInterval(this.inactivityTimer);
                },

                startOpponentInactivityTimer(turnStartedAt) {
                    this.stopOpponentInactivityTimer();

                    const updateOpponentInactivityTime = () => {
                        if (!turnStartedAt) return;

                        const now = Math.floor(Date.now() / 1000);
                        const elapsed = now - turnStartedAt;
                        this.opponentInactivityTimeRemaining = Math.max(0, 120 - elapsed);

                        if (this.opponentInactivityTimeRemaining <= 0) {
                            this.freezeTime();
                            alert('Ο αντίπαλός σας έχει μείνει ανενεργός για πολύ ώρα. Το παιχνίδι θα κατακυρωθεί υπέρ σας.');
                            this.loading = true;
                            this.forfeitGame(true);
                        }
                    };

                    updateOpponentInactivityTime();
                    this.opponentInactivityTimer = setInterval(updateOpponentInactivityTime, 1000);
                },

                stopOpponentInactivityTimer() {
                    if (this.opponentInactivityTimer) {
                        clearInterval(this.opponentInactivityTimer);
                        this.opponentInactivityTimer = null;
                    }
                    this.opponentInactivityTimeRemaining = 120;
                },

                resetForNextRound() {
                    this.answer = '';
                    this.topFiveAnswers = ['', '', '', '', ''];
                    this.currentQuestion = null;
                    this.currentCategory = null;
                    this.availableDifficulties = { easy: true, medium: true, hard: true };
                    this.lastResult = null;
                    this.questionTimerReady = false;
                    this.timeRemaining = 60;
                    this.phase = 'waiting';
                    this.isMyTurn = false;
                    this.opponentMove = null;
                },

                setupEcho() {
                    this.echoChannel = window.Echo.channel(`game.${this.game.id}`);

                    this.echoChannel.listen('GameStateUpdated', (e) => {
                        this.handleGameStateUpdate(e);
                    });

                    this.echoChannel.listen('TurnChanged', (e) => {
                        this.handleTurnChange(e);
                    });

                    this.echoChannel.listen('OpponentMoved', (e) => {
                        this.handleOpponentMove(e);
                    });

                    this.echoChannel.listen('GameCompleted', (e) => {
                        this.handleGameCompleted(e);
                    });
                },

                handleGameStateUpdate(data) {
                    this.game = data.game;
                    this.usedCombinations = data.game.used_combinations || [];
                    this.turnStartedAt = data.game.turn_started_at;
                    this.updatePlayers();
                    this.loadGameHistory();

                    const wasMyTurn = this.isMyTurn;
                    this.isMyTurn = data.game.current_turn_player_id === this.player.id;

                    if (this.isMyTurn !== wasMyTurn) {
                        if (this.isMyTurn) {
                            this.opponentMove = null;
                            this.stopOpponentTimer();
                            this.stopOpponentInactivityTimer();

                            if (this.phase !== 'result') {
                                this.phase = 'category';
                                this.startInactivityTimer();
                            }
                        } else {
                            this.stopInactivityTimer();
                            if (this.phase !== 'result') {
                                this.phase = 'waiting';
                            }
                            if (this.turnStartedAt) {
                                this.startOpponentInactivityTimer(this.turnStartedAt);
                            }
                        }
                    }
                },

                handleTurnChange(data) {
                    const wasMyTurn = this.isMyTurn;
                    this.isMyTurn = data.currentPlayerId === this.player.id;
                    this.turnStartedAt = data.turnStartedAt;

                    if (this.isMyTurn) {
                        this.opponentMove = null;
                        this.stopOpponentTimer();
                        this.stopOpponentInactivityTimer();
                    }

                    if (this.phase !== 'result') {
                        if (this.isMyTurn && this.phase === 'waiting') {
                            this.phase = 'category';
                            this.startInactivityTimer();
                        } else if (!this.isMyTurn && wasMyTurn) {
                            this.stopInactivityTimer();
                            this.phase = 'waiting';
                        }
                    }

                    if (!this.isMyTurn && data.turnStartedAt) {
                        this.startOpponentInactivityTimer(data.turnStartedAt);
                    }
                },

                handleOpponentMove(data) {
                    if (data.move.player_id !== this.player.id) {
                        this.opponentMove = data.move;
                        console.log(this.opponentMove)

                        if (data.move.phase === 'difficulty' && data.move.started_at) {
                            this.startOpponentTimer(data.move.started_at);
                        } else {
                            this.stopOpponentTimer();
                        }
                    }
                },

                handleGameCompleted(data) {
                    localStorage.removeItem(`game_${this.game.id}_tabSwitchCount`);
                    localStorage.removeItem(`game_${this.game.id}_consecutiveTimeouts`);

                    if (this.echoChannel) {
                        window.Echo.leave(`game.${this.game.id}`);
                    }

                    window.location.href = `/game/${this.game.id}/results`;
                },

                async forfeitGame(forcedForfeit = false) {
                    if (forcedForfeit) {
                        await new Promise(r => setTimeout(r, 2000));
                    }

                    this.stopTimer();
                    this.stopOpponentTimer();

                    if (this.echoChannel) {
                        window.Echo.leave(`game.${this.game.id}`);
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
                        this.alreadyForfeit = true;
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
                    if (this.currentQuestion.type === 'text_input' || this.currentQuestion.type === 'text_input_with_image') {
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
                        'easy': 'Εύκολο (1π)',
                        'medium': 'Μέτριο (3π)',
                        'hard': 'Δύσκολο (3π)'
                    };
                    return labels[difficulty] || difficulty;
                },

                cleanup() {
                    // Cleanup Echo channel when component is destroyed
                    if (this.echoChannel) {
                        window.Echo.leave(`game.${this.game.id}`);
                        this.echoChannel = null;
                    }

                    // this.stopTimer();
                    // this.stopOpponentTimer();
                    // this.stopInactivityTimer();
                    // this.stopOpponentInactivityTimer();
                }
            }
        }
    </script>

    <div class="py-4 no-select"
         x-data="gameBoard(@js($game), @js($player), @js($categories), {{ $isPlayerTurn ? 'true' : 'false' }}, @js($usedCombinations), @js($activeRound), {{ $turnStartedAt ?? 'null' }})"
{{--         x-init="setTimeout(() => { $refs.loadingOverlay.style.display = 'none'; }, 2000)"--}}
         @beforeunload.window="cleanup()">
        <!-- Loading Overlay -->
{{--        <div x-ref="loadingOverlay" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-50/95 backdrop-blur-sm">--}}
{{--            <div class="text-center">--}}
{{--                <div class="inline-flex items-center gap-2 mb-3">--}}
{{--                    <div class="w-3 h-3 bg-indigo-400 rounded-full animate-bounce" style="animation-delay: 0ms"></div>--}}
{{--                    <div class="w-3 h-3 bg-purple-400 rounded-full animate-bounce" style="animation-delay: 150ms"></div>--}}
{{--                    <div class="w-3 h-3 bg-pink-400 rounded-full animate-bounce" style="animation-delay: 300ms"></div>--}}
{{--                </div>--}}
{{--                <p class="text-lg font-semibold text-slate-700">Το παιχνίδι φορτώνει...</p>--}}
{{--            </div>--}}
{{--        </div>--}}

        <div x-show="loading" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-50/45 backdrop-blur-sm">
            <div class="text-center">
                <div class="inline-flex items-center gap-2 mb-3">
                    <div class="w-3 h-3 bg-indigo-400 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                    <div class="w-3 h-3 bg-purple-400 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                    <div class="w-3 h-3 bg-pink-400 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-2">
            <div class="flex flex-col items-center justify-center py-3">
                <a href="#" class="text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-indigo-500 to-purple-500 hover:from-indigo-600 hover:to-purple-600 transition-all duration-200">
                    quizball.io
                </a>
            </div>

            <div x-show="isMyTurn && phase !== 'result'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="mt-4 mx-auto sm:max-w-md lg:max-w-lg">
                <div class="relative overflow-hidden bg-gradient-to-r from-emerald-500 via-green-500 to-emerald-600 rounded-xl shadow-lg">
                    <!-- Animated background shine -->
                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent animate-shine"></div>

                    <div class="relative px-1 py-4 flex items-center justify-center gap-3">
{{--                        <div class="relative">--}}
{{--                            <div class="w-3 h-3 bg-white rounded-full animate-pulse"></div>--}}
{{--                            <div class="absolute inset-0 w-3 h-3 bg-white rounded-full animate-ping"></div>--}}
{{--                        </div>--}}

                        <span class="text-white font-black lg:text-xl sm:text-sm tracking-wide drop-shadow-lg">
                            Η ΜΠΑΛΑ ΕΙΝΑΙ ΣΤΟ ΓΗΠΕΔΟ ΣΟΥ!
                        </span>

                        <div class="relative w-8 h-8 animate-bounce">
                            <div class="animate-bounce text-2xl mb-4">⚽</div>
                            <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-6 h-1 bg-black/30 rounded-full blur-sm animate-pulse"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
            @keyframes shine {
                0% { transform: translateX(-100%); }
                100% { transform: translateX(100%); }
            }
            .animate-shine {
                animation: shine 2s infinite;
            }
        </style>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-32 lg:pb-0">
            <div class="flex flex-col lg:flex-row gap-2 lg:gap-6">
                <div class="w-full lg:w-80 flex-shrink-0 space-y-3 lg:space-y-4">
                    <div class="lg:hidden fixed bottom-0 left-0 right-0 z-40 shadow-2xl" style="background: linear-gradient(180deg, rgba(255,255,255,0.98) 0%, rgba(249,250,251,0.98) 100%); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);">
                        <div class="border-t-3 border-gradient-to-r from-purple-400 via-pink-400 to-indigo-400" style="border-image: linear-gradient(90deg, #c084fc, #f472b6, #818cf8) 1;">
                            <div class="p-2">
                                <!-- Score Cards Row -->
                                <div class="grid grid-cols-2 gap-2 mb-2">
                                    <div class="relative overflow-hidden rounded-xl transition-all duration-300"
                                         :class="game.current_turn_player_id === {{ $player->id }}
                                             ? 'bg-gradient-to-br from-emerald-400 to-emerald-600 shadow-lg shadow-emerald-500/50'
                                             : 'bg-gradient-to-br from-slate-200 to-slate-300 shadow-md'">
                                        <div class="absolute inset-0 bg-gradient-to-tr from-transparent via-white/20 to-transparent"></div>

                                        <div class="relative px-2 py-1.5">
                                            <div x-show="game.current_turn_player_id === {{ $player->id }}"
                                                 class="absolute top-2 right-2 w-1.5 h-1.5 bg-white rounded-full animate-pulse shadow-lg"></div>

                                            <div class="text-[8px] font-black uppercase tracking-wider mb-0.5"
                                                 :class="game.current_turn_player_id === {{ $player->id }} ? 'text-white/90' : 'text-slate-600'">
                                                ΕΣΥ
                                            </div>
                                            <div class="text-2xl font-black leading-none mb-0.5"
                                                 :class="game.current_turn_player_id === {{ $player->id }} ? 'text-white drop-shadow-md' : 'text-slate-700'"
                                                 x-text="players[{{ $player->id }}]?.score || 0"></div>
                                            <div class="text-[7px] font-semibold uppercase tracking-wide"
                                                 :class="game.current_turn_player_id === {{ $player->id }} ? 'text-emerald-100' : 'text-slate-500'">
                                                ΠΟΝΤΟΙ
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Opponent Score -->
                                    <div x-show="opponent"
                                         class="relative overflow-hidden rounded-xl transition-all duration-300"
                                         :class="game.current_turn_player_id !== {{ $player->id }}
                                             ? 'bg-gradient-to-br from-indigo-400 to-indigo-600 shadow-lg shadow-indigo-500/50'
                                             : 'bg-gradient-to-br from-slate-200 to-slate-300 shadow-md'">
                                        <!-- Shine Effect -->
                                        <div class="absolute inset-0 bg-gradient-to-tr from-transparent via-white/20 to-transparent"></div>

                                        <div class="relative px-2 py-1.5">
                                            <!-- Turn Indicator Dot -->
                                            <div x-show="game.current_turn_player_id !== {{ $player->id }}"
                                                 class="absolute top-2 right-2 w-1.5 h-1.5 bg-white rounded-full animate-pulse shadow-lg"></div>

                                            <div class="text-[8px] font-black uppercase tracking-wider mb-0.5"
                                                 :class="game.current_turn_player_id !== {{ $player->id }} ? 'text-white/90' : 'text-slate-600'">
                                                ΑΝΤΙΠΑΛΟΣ
                                            </div>
                                            <div class="text-2xl font-black leading-none mb-0.5"
                                                 :class="game.current_turn_player_id !== {{ $player->id }} ? 'text-white drop-shadow-md' : 'text-slate-700'"
                                                 x-text="opponent?.score || 0"></div>
                                            <div class="text-[7px] font-semibold uppercase tracking-wide"
                                                 :class="game.current_turn_player_id !== {{ $player->id }} ? 'text-indigo-100' : 'text-slate-500'">
                                                ΠΟΝΤΟΙ
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Forfeit Button & Round Info Row -->
                                <div class="flex items-stretch gap-2">
                                    <button @click="showForfeitConfirmation = true"
                                            class="relative flex-shrink-0 w-14 h-14 rounded-lg overflow-hidden group shadow-lg"
                                            style="background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%); box-shadow: inset 0 -2px 8px rgba(0,0,0,0.2);">
                                        <div class="absolute inset-0 bg-white/20 animate-pulse"></div>

                                        <div class="relative h-full flex items-center justify-center transform transition-transform duration-200 group-active:scale-90">
                                            <svg class="w-8 h-8 text-white drop-shadow-lg" viewBox="-28 -28 336.00 336.00"
                                                 xml:space="preserve" transform="matrix(-1, 0, 0, 1, 0, 0)rotate(0)"
                                                 stroke="#000000" stroke-width="0.0028">
                                                <g id="SVGRepo_bgCarrier" stroke-width="0"/>
                                                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"
                                                   stroke="#CCCCCC" stroke-width="0.56"/>
                                                <g id="SVGRepo_iconCarrier">
                                                    <path color-rendering="auto" image-rendering="auto" shape-rendering="auto"
                                                          color-interpolation="sRGB"
                                                          d="M5,65 c-2.761,0-5,2.239-5,5v30c0,2.195,1.431,4.133,3.529,4.779l126.564,38.943C132.04,183.362,164.886,215,205,215 c13.7,0,26.544-3.707,37.607-10.146c4.007,7.062,12.035,11.136,20.272,9.938c9.32-1.356,16.474-9.106,17.08-18.504 c0.5-7.757-3.573-14.963-10.108-18.676C276.292,166.547,280,153.702,280,140c0-41.362-33.638-75-75-75h-70c-2.761,0-5,2.239-5,5v5 h-30v-5c0-2.761-2.239-5-5-5H5L5,65z M10,75h80v5c0,2.761,2.239,5,5,5h40c2.761,0,5-2.239,5-5v-5h27.652 c-20.658,11.914-35.129,33.375-37.34,58.326L10,96.307L10,75L10,75z M205,75c35.958,0,65,29.042,65,65c0,35.958-29.042,65-65,65 c-35.958,0-65-29.042-65-65C140,104.042,169.042,75,205,75L205,75z M205,85c-2.761-0.039-5.032,2.168-5.071,4.929 c-0.039,2.761,2.168,5.032,4.929,5.071c0.047,0.001,0.094,0.001,0.141,0c24.912,0,45,20.088,45,45 c-0.039,2.761,2.168,5.032,4.929,5.071c2.761,0.039,5.032-2.168,5.071-4.929c0.001-0.047,0.001-0.094,0-0.141 C260,109.684,235.317,85,205,85L205,85z M264.229,185.94c3.701,1.713,6.02,5.512,5.75,9.703c-0.306,4.743-3.836,8.57-8.539,9.254 c-4.427,0.644-8.647-1.677-10.5-5.67C255.904,195.368,260.37,190.904,264.229,185.94z"/>
                                                </g>
                                            </svg>
                                        </div>
                                    </button>

                                    <!-- History Button -->
                                    <button @click="showMoveHistory = true"
                                            class="relative flex-shrink-0 w-14 h-14 rounded-lg overflow-hidden group shadow-lg"
                                            style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);">
                                        <div class="absolute inset-0 bg-gradient-to-br from-white/30 via-transparent to-black/20"></div>

                                        <div class="relative h-full flex items-center justify-center transform transition-transform duration-200 group-active:scale-90">
                                            <svg class="w-7 h-7 text-white drop-shadow-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                    </button>

                                    <!-- Round Info Banner -->
                                    <div class="flex-1 relative overflow-hidden rounded-lg shadow-lg" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);">
                                        <div class="absolute inset-0 bg-gradient-to-br from-white/20 via-transparent to-black/10"></div>
                                        <div class="absolute inset-0 animate-pulse bg-white/5"></div>
                                        <div class="relative h-14 flex items-center justify-center gap-2">

                                            <span class="text-white font-bold text-base tracking-wide drop-shadow-lg">
                                                ΓΥΡΟΣ <span x-text="game.current_round || 1"></span>/<span x-text="game.max_rounds"></span>
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Power-up Buttons -->
                                    <button :disabled="!(phase === 'category' || phase === 'difficulty' || phase === 'waiting')"
                                            :class="!(phase === 'category' || phase === 'difficulty' || phase === 'waiting') ? 'opacity-50 cursor-not-allowed' : ''"
                                            class="relative flex-shrink-0 w-14 h-14 rounded-lg overflow-hidden group shadow-lg transition-opacity duration-200"
                                            style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                                        <div class="absolute inset-0 bg-gradient-to-br from-white/30 via-transparent to-black/20"></div>
                                        <div class="relative h-full flex items-center justify-center transform transition-transform duration-200 group-active:scale-90">
                                            <span class="text-white font-black text-lg drop-shadow-lg">2X</span>
                                        </div>
                                    </button>

                                    <button :disabled="!(phase === 'question' && currentQuestion)"
                                            :class="!(phase === 'question' && currentQuestion) ? 'opacity-50 cursor-not-allowed' : ''"
                                            class="relative flex-shrink-0 w-14 h-14 rounded-lg overflow-hidden group shadow-lg transition-opacity duration-200"
                                            style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                                        <div class="absolute inset-0 bg-gradient-to-br from-white/30 via-transparent to-black/20"></div>
                                        <div class="relative h-full flex flex-col items-center justify-center transform transition-transform duration-200 group-active:scale-90">
                                            <span class="text-white font-black text-base leading-none drop-shadow-lg">50</span>
                                            <span class="text-white font-black text-base leading-none drop-shadow-lg">50</span>
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Inactivity Timer -->
                    <div x-show="phase !== 'question' && phase !== 'result' && isMyTurn"
                         class="rounded-xl p-3 lg:p-4 shadow-lg transition-all duration-300"
                         :class="isMyTurn ? (showInactivityWarning ? 'bg-gradient-to-r from-red-50 to-pink-50 border-2 border-red-400' : 'bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-300') : (showInactivityWarning ? 'bg-gradient-to-r from-amber-50 to-yellow-50 border-2 border-yellow-400' : 'bg-gradient-to-r from-slate-50 to-gray-50 border-2 border-gray-300')"
                         x-bind:style="window.innerWidth >= 1024 ? 'margin-top: 0 !important;' : ''">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
{{--                                <div class="w-2 h-2 rounded-full animate-pulse"--}}
{{--                                     :class="showInactivityWarning ? 'bg-red-500' : 'bg-blue-500'"></div>--}}
                                <span class="font-semibold text-gray-800 text-xs lg:text-sm">Υπολειπόμενος χρόνος</span>
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
                            <span x-show="isMyTurn">Ο χρόνος εξαντλείται!</span>
                            <span x-show="!isMyTurn">Ο χρόνος του αντιπάλου εξαντλείται!</span>
                        </div>
                    </div>



                    <!-- Desktop: Vertical Score Display -->
                    <div class="hidden lg:block bg-white/80 backdrop-blur-sm rounded-2xl shadow-sm p-5 border border-slate-200">
                        <h3 class="text-sm font-medium text-slate-600 uppercase tracking-wide mb-4">Σκορ</h3>
                        <div class="space-y-3">
                            <div class="p-4 rounded-xl transition-all duration-300"
                                 :class="game.current_turn_player_id === {{ $player->id }} ? 'bg-emerald-50 ring-1 ring-emerald-300' : 'bg-slate-50'">
                                <div class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">ΕΣΥ</div>
                                <div class="text-3xl font-bold text-emerald-600" x-text="players[{{ $player->id }}]?.score || 0"></div>
                                <div class="text-xs text-slate-600 mt-1 truncate" x-text="players[{{ $player->id }}]?.display_name"></div>
                            </div>
                            <div class="p-4 rounded-xl transition-all duration-300"
                                 :class="game.current_turn_player_id !== {{ $player->id }} ? 'bg-indigo-50 ring-1 ring-indigo-300' : 'bg-slate-50'" x-show="opponent">
                                <div class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">ΑΝΤΙΠΑΛΟΣ</div>
                                <div class="text-3xl font-bold text-indigo-600" x-text="opponent?.score || 0"></div>
                                <div class="text-xs text-slate-600 mt-1 truncate" x-text="opponent?.display_name"></div>
                            </div>
                        </div>
                        <div class="text-center mt-3 text-md lg:text-md text-slate-600">
                            <strong>Γύρος <span class="font-semibold" x-text="game.current_round"></span>/<span x-text="game.max_rounds"></span></strong>
                        </div>
                    </div>

                    <!-- Power-up Buttons (Desktop Only) -->
                    <div class="hidden lg:grid grid-cols-2 gap-3">
                        <button :disabled="!(phase === 'category' || phase === 'difficulty' || phase === 'waiting')"
                                :class="!(phase === 'category' || phase === 'difficulty' || phase === 'waiting') ? 'opacity-50 cursor-not-allowed' : 'hover:shadow-lg'"
                                class="relative overflow-hidden rounded-xl shadow-md transition-all duration-200 p-4"
                                style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                            <div class="absolute inset-0 bg-gradient-to-br from-white/30 via-transparent to-black/20"></div>
                            <div class="relative flex flex-col items-center justify-center gap-1">
                                <span class="text-white font-black text-3xl drop-shadow-lg">2X</span>
                                <span class="text-white text-xs font-semibold drop-shadow">Διπλοί Πόντοι</span>
                            </div>
                        </button>

                        <button :disabled="!(phase === 'question' && currentQuestion)"
                                :class="!(phase === 'question' && currentQuestion) ? 'opacity-50 cursor-not-allowed' : 'hover:shadow-lg'"
                                class="relative overflow-hidden rounded-xl shadow-md transition-all duration-200 p-4"
                                style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                            <div class="absolute inset-0 bg-gradient-to-br from-white/30 via-transparent to-black/20"></div>
                            <div class="relative flex flex-col items-center justify-center gap-1">
                                <div class="flex items-center gap-1">
                                    <span class="text-white font-black text-2xl leading-none drop-shadow-lg">50</span>
                                    <span class="text-white font-black text-xs leading-none drop-shadow-lg">/</span>
                                    <span class="text-white font-black text-2xl leading-none drop-shadow-lg">50</span>
                                </div>
                                <span class="text-white text-xs font-semibold drop-shadow">Μισές Επιλογές</span>
                            </div>
                        </button>
                    </div>

                    <!-- History Button (Desktop Only) -->
                    <button @click="showMoveHistory = true"
                            class="hidden lg:flex w-full items-center justify-center gap-2 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-semibold py-2.5 px-5 rounded-xl shadow-md hover:shadow-lg transition-all duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Ιστορικό Κινήσεων
                    </button>

                    <!-- Forfeit Button (Desktop Only) -->
                    <button @click="showForfeitConfirmation = true"
                            class="hidden lg:flex w-full items-center justify-center gap-2 bg-white border-2 border-red-300 hover:border-red-500 text-red-600 hover:text-red-700 font-semibold py-2.5 px-5 rounded-xl shadow-sm hover:shadow-md transition-all duration-200">
                        <svg class="w-10 h-10 text-white drop-shadow-lg" viewBox="-28 -28 336.00 336.00"
                             xml:space="preserve" transform="matrix(-1, 0, 0, 1, 0, 0)rotate(0)"
                             stroke="#000000" stroke-width="0.0028">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"/>
                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"
                               stroke="#CCCCCC" stroke-width="0.56"/>
                            <g id="SVGRepo_iconCarrier">
                                <path color-rendering="auto" image-rendering="auto" shape-rendering="auto"
                                      color-interpolation="sRGB"
                                      d="M5,65 c-2.761,0-5,2.239-5,5v30c0,2.195,1.431,4.133,3.529,4.779l126.564,38.943C132.04,183.362,164.886,215,205,215 c13.7,0,26.544-3.707,37.607-10.146c4.007,7.062,12.035,11.136,20.272,9.938c9.32-1.356,16.474-9.106,17.08-18.504 c0.5-7.757-3.573-14.963-10.108-18.676C276.292,166.547,280,153.702,280,140c0-41.362-33.638-75-75-75h-70c-2.761,0-5,2.239-5,5v5 h-30v-5c0-2.761-2.239-5-5-5H5L5,65z M10,75h80v5c0,2.761,2.239,5,5,5h40c2.761,0,5-2.239,5-5v-5h27.652 c-20.658,11.914-35.129,33.375-37.34,58.326L10,96.307L10,75L10,75z M205,75c35.958,0,65,29.042,65,65c0,35.958-29.042,65-65,65 c-35.958,0-65-29.042-65-65C140,104.042,169.042,75,205,75L205,75z M205,85c-2.761-0.039-5.032,2.168-5.071,4.929 c-0.039,2.761,2.168,5.032,4.929,5.071c0.047,0.001,0.094,0.001,0.141,0c24.912,0,45,20.088,45,45 c-0.039,2.761,2.168,5.032,4.929,5.071c2.761,0.039,5.032-2.168,5.071-4.929c0.001-0.047,0.001-0.094,0-0.141 C260,109.684,235.317,85,205,85L205,85z M264.229,185.94c3.701,1.713,6.02,5.512,5.75,9.703c-0.306,4.743-3.836,8.57-8.539,9.254 c-4.427,0.644-8.647-1.677-10.5-5.67C255.904,195.368,260.37,190.904,264.229,185.94z"/>
                            </g>
                                    </svg>
                        Λήξη Παιχνιδιού
                    </button>


                </div>

                <!-- Main Content Area -->
                <div class="flex-1 min-w-0">

                    <!-- Game Phase Display -->
                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-sm p-3 lg:p-4 border border-slate-200">
                        <div x-show="phase === 'category' || phase === 'difficulty' || phase === 'waiting'" x-cloak style="display:none;">
                            <h3 class="text-base lg:text-lg font-semibold text-slate-800 mb-3 lg:mb-4">Επίλεξε Κατηγορία & Επίπεδο</h3>

                            <!-- List View (Mobile & Desktop) -->
                            <div class="space-y-3">
                                <template x-for="(category, index) in categories" :key="category.id">
                                    <div @click="!isCategoryUsed(category.id) && isMyTurn && !loading ? null : null"
                                         :class="[
                                     isCategoryUsed(category.id) ? 'opacity-50 grayscale pointer-events-none' : 'hover:shadow-lg transform hover:-translate-y-0.5',
                                     index % 6 === 0 ? 'bg-gradient-to-r from-amber-700 to-amber-600' : '',
                                     index % 6 === 1 ? 'bg-gradient-to-r from-blue-600 to-blue-500' : '',
                                     index % 6 === 2 ? 'bg-gradient-to-r from-green-600 to-green-500' : '',
                                     index % 6 === 3 ? 'bg-gradient-to-r from-purple-600 to-purple-500' : '',
                                     index % 6 === 4 ? 'bg-gradient-to-r from-rose-600 to-rose-500' : '',
                                     index % 6 === 5 ? 'bg-gradient-to-r from-teal-600 to-teal-500' : ''
                                 ]"
                                         class="rounded-2xl shadow-md p-2 lg:p-2 transition-all duration-300 cursor-pointer">

                                        <div class="flex items-center justify-between gap-2 lg:gap-4">
                                            <!-- Left: Icon & Name -->
                                            <div class="flex items-center gap-2 lg:gap-3 flex-1 min-w-0 overflow-hidden">
                                                <div class="text-2xl lg:text-4xl flex-shrink-0" x-text="category.icon"></div>
                                                <span class="font-bold text-sm sm:text-base lg:text-xl text-white break-words hyphens-auto"
                                                      style="word-break: break-word; overflow-wrap: break-word; line-height: 1.2;"
                                                      x-text="category.name"></span>
                                            </div>

                                            <!-- Right: Circular Badges -->
                                            <div class="flex items-center gap-1.5 lg:gap-2 flex-shrink-0">
                                                <!-- x1 - Easy -->
                                                <button @click.stop="selectCategoryAndDifficulty(category.id, 'easy')"
                                                        :disabled="!isMyTurn || loading || !isCategoryDifficultyAvailable(category.id, 'easy')"
                                                        :class="isCategoryDifficultyAvailable(category.id, 'easy')
                                                    ? 'bg-white text-gray-800 hover:scale-110 hover:ring-4 hover:ring-white/50'
                                                    : 'bg-gray-400 text-gray-600 cursor-not-allowed opacity-40'"
                                                        class="w-10 h-10 sm:w-12 sm:h-12 lg:w-14 lg:h-14 rounded-full font-bold text-xs sm:text-sm lg:text-base shadow-lg transition-all duration-200 disabled:cursor-not-allowed flex items-center justify-center">
                                                    x1
                                                </button>

                                                <!-- x2 - Medium -->
                                                <button @click.stop="selectCategoryAndDifficulty(category.id, 'medium')"
                                                        :disabled="!isMyTurn || loading || !isCategoryDifficultyAvailable(category.id, 'medium')"
                                                        :class="isCategoryDifficultyAvailable(category.id, 'medium')
                                                    ? 'bg-white text-gray-800 hover:scale-110 hover:ring-4 hover:ring-white/50'
                                                    : 'bg-gray-400 text-gray-600 cursor-not-allowed opacity-40'"
                                                        class="w-10 h-10 sm:w-12 sm:h-12 lg:w-14 lg:h-14 rounded-full font-bold text-xs sm:text-sm lg:text-base shadow-lg transition-all duration-200 disabled:cursor-not-allowed flex items-center justify-center">
                                                    x2
                                                </button>

                                                <!-- x3 - Hard -->
                                                <button @click.stop="selectCategoryAndDifficulty(category.id, 'hard')"
                                                        :disabled="!isMyTurn || loading || !isCategoryDifficultyAvailable(category.id, 'hard')"
                                                        :class="isCategoryDifficultyAvailable(category.id, 'hard')
                                                    ? 'bg-white text-gray-800 hover:scale-110 hover:ring-4 hover:ring-white/50'
                                                    : 'bg-gray-400 text-gray-600 cursor-not-allowed opacity-40'"
                                                        class="w-10 h-10 sm:w-12 sm:h-12 lg:w-14 lg:h-14 rounded-full font-bold text-xs sm:text-sm lg:text-base shadow-lg transition-all duration-200 disabled:cursor-not-allowed flex items-center justify-center">
                                                    x3
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div x-show="phase === 'question' && currentQuestion" x-cloak style="display: none">
                            <div class="mb-6">
                                <div class="mb-6 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-4 border border-blue-200 transition-all duration-300"
                                     :class="!questionTimerReady ? 'blur-sm' : ''">
                                    <div class="flex justify-between text-sm mb-2">
                                        <span class="font-semibold text-gray-700">Υπολειπόμενος χρόνος</span>
                                        <span class="font-bold text-blue-600 text-lg" x-text="timeRemaining + 's'"></span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-3">
                                        <div class="h-3 rounded-full transition-all duration-1000"
                                             :class="timeRemaining > 30 ? 'bg-gradient-to-r from-blue-400 to-blue-600' : (timeRemaining > 10 ? 'bg-gradient-to-r from-yellow-400 to-amber-500' : 'bg-gradient-to-r from-red-400 to-red-600')"
                                             :style="`width: ${(timeRemaining / 60) * 100}%`"></div>
                                    </div>
                                </div>

                                <h3 class="text-2xl font-bold text-gray-900 leading-relaxed" x-text="currentQuestion?.text"></h3>
                                <h3 class="text-sm font-bold text-gray-500 mb-6 leading-relaxed" x-text="'H Ερώτηση δημιουργήθηκε από τον χρήστη: ' + currentQuestion?.created_by"></h3>

                                <div x-show="currentQuestion?.type === 'text_input_with_image'" class="mb-6">
                                    <div class="relative bg-gradient-to-br from-gray-50 to-slate-100 rounded-xl p-4 shadow-lg border-2 border-gray-200">
                                        <img :src="'/' + currentQuestion?.image_url"
                                             style="max-width: 350px;"
                                             alt="Question image"
                                             class="w-full max-w-2xl mx-auto rounded-lg shadow-md border border-gray-300">
                                    </div>
                                </div>

                                <div x-show="currentQuestion?.type === 'text_input'" class="space-y-4">
                                    <input type="text" x-model="answer"
                                           @keydown.enter="submitAnswer"
                                           class="w-full rounded-xl border-2 border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 text-lg p-4 transition-all duration-200"
                                           placeholder="Type your answer...">
                                </div>

                                <div x-show="currentQuestion?.type === 'text_input_with_image'" class="space-y-4">
                                    <input type="text" x-model="answer"
                                           @keydown.enter="submitAnswer"
                                           class="w-full rounded-xl border-2 border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 text-lg p-4 transition-all duration-200"
                                           placeholder="Type your answer...">
                                </div>

                                <div x-show="currentQuestion?.type === 'multiple_choice'" class="space-y-3">
                                    <template x-for="(ans, index) in currentQuestion?.answers" :key="ans.id">
                                        <button @click="answer = ans.id"
                                                :class="answer === ans.id ? 'border-blue-500 bg-gradient-to-r from-blue-50 to-indigo-50 ring-2 ring-blue-400 scale-105' : 'border-gray-300 hover:border-blue-300 hover:bg-blue-50/30'"
                                                class="w-full p-5 border-2 rounded-xl transition-all duration-200 text-left shadow-sm hover:shadow-md">
                                            <span class="font-bold text-blue-600 mr-3" x-text="String.fromCharCode(65 + index)"></span>
                                            <span class="text-gray-900 font-medium" x-text="ans.answer_text"></span>
                                        </button>
                                    </template>
                                </div>

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

                            <div class="flex gap-3">
                                <button @click="passQuestion"
                                        :disabled="loading"
                                        class="flex-shrink-0 bg-gray-100 hover:bg-gray-200 border-2 border-gray-300 hover:border-gray-400 text-gray-700 hover:text-gray-900 font-bold py-4 px-6 rounded-xl shadow-md hover:shadow-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none transform hover:scale-105">
                                    <span class="hidden sm:inline">Παράλειψη</span>
                                    <svg class="sm:hidden w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path>
                                    </svg>
                                </button>
                                <button @click="submitAnswer"
                                        :disabled="!canSubmit || loading"
                                        class="flex-1 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-bold py-4 px-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none transform hover:scale-105">
                                    Υποβολή Απάντησης
                                </button>
                            </div>
                        </div>

                        <div x-show="phase === 'result' && lastResult" x-cloak>
                            <div class="py-6 space-y-6">
                                <div class="mb-6">
                                    <div x-show="lastResult?.is_correct"
                                         x-transition:enter="transition ease-out duration-500"
                                         x-transition:enter-start="opacity-0 scale-75 -translate-y-4"
                                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                         class="w-full flex items-center gap-4 bg-gradient-to-r from-emerald-50 to-emerald-100 border-2 border-emerald-400 rounded-2xl px-6 py-6 shadow-xl">
                                        <div class="flex-shrink-0">
                                            <div class="w-20 h-20 lg:w-24 lg:h-24 rounded-full bg-emerald-500 flex items-center justify-center shadow-lg transform transition-transform hover:scale-110 animate-pulse">
                                                <div class="text-5xl lg:text-6xl text-white font-black">✓</div>
                                            </div>
                                        </div>
                                        <div class="flex-1 text-left">
                                            <div class="text-emerald-900 font-black text-3xl lg:text-4xl mb-2">Σωστό!</div>
                                            <div class="text-emerald-700 font-bold text-xl lg:text-2xl">
                                                +<span x-text="lastResult?.points_earned"></span> πόντοι
                                            </div>
                                        </div>
                                        <div class="flex-shrink-0 text-7xl lg:text-8xl animate-bounce">
                                            🎉
                                        </div>
                                    </div>

                                    <div x-show="!lastResult?.is_correct"
                                         x-transition:enter="transition ease-out duration-500"
                                         x-transition:enter-start="opacity-0 scale-75 -translate-y-4"
                                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                         class="w-full flex items-center gap-4 bg-gradient-to-r from-rose-50 to-rose-100 border-2 border-rose-400 rounded-2xl px-6 py-6 shadow-xl">
                                        <div class="flex-shrink-0">
                                            <div class="w-20 h-20 lg:w-24 lg:h-24 rounded-full bg-rose-500 flex items-center justify-center shadow-lg transform transition-transform hover:scale-110">
                                                <div class="text-5xl lg:text-6xl text-white font-black">×</div>
                                            </div>
                                        </div>
                                        <div class="flex-1 text-left">
                                            <div class="text-rose-900 font-black text-3xl lg:text-4xl mb-2">Λάθος</div>
                                            <div class="text-rose-700 font-bold text-xl lg:text-2xl">
                                                0 πόντοι
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <div class="bg-gradient-to-r from-slate-50 to-gray-50 rounded-xl p-5 border border-slate-200 shadow-sm">
                                    <div class="text-xs text-slate-500 uppercase tracking-wide font-semibold mb-2">ΕΡΩΤΗΣΗ</div>
                                    <div class="text-slate-900 font-medium text-base lg:text-lg leading-relaxed" x-text="lastResult?.question_text"></div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="bg-white rounded-xl p-5 shadow-lg border-2 border-slate-200 transform transition-all hover:scale-105">
                                        <div class="text-xs text-slate-500 uppercase tracking-wide font-semibold mb-3">Η ΑΠΑΝΤΗΣΗ ΣΟΥ</div>
                                        <div class="text-slate-900 font-bold text-lg break-words" x-text="lastResult?.player_answer || '-'"></div>
                                    </div>
                                    <div class="bg-white rounded-xl p-5 shadow-lg border-2 border-emerald-200 transform transition-all hover:scale-105">
                                        <div class="text-xs text-slate-500 uppercase tracking-wide font-semibold mb-3">ΣΩΣΤΗ ΑΠΑΝΤΗΣΗ</div>
                                        <div class="text-emerald-700 font-bold text-lg break-words" x-text="lastResult?.correct_answer"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="showTabSwitchWarning"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 transform transition-all">
                <div class="text-center" >
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full  mb-4" x-show="tabSwitchCount == 1">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" width="256" height="256" viewBox="0 0 256 256" xml:space="preserve">
                            <g style="stroke: none; stroke-width: 0; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: none; fill-rule: nonzero; opacity: 1;" transform="translate(1.4065934065934016 1.4065934065934016) scale(2.81 2.81)">
                                <path d="M 56.35 62.537 l 0.63 -3.584 c 0.575 -3.271 3.722 -5.477 6.993 -4.902 h 0 c 3.271 0.575 5.477 3.722 4.902 6.993 l -1.345 7.647 L 64.085 90 h -7.517 C 53.808 80.129 53.398 70.884 56.35 62.537 z" style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(253,218,170); fill-rule: nonzero; opacity: 1;" transform=" matrix(1 0 0 1 0 0) " stroke-linecap="round"/>
                                <path d="M 54.932 62.504 c 3.739 0 6.771 -3.031 6.771 -6.771 V 6.771 C 61.703 3.031 58.672 0 54.932 0 H 36.04 c -4.052 0 -7.337 3.285 -7.337 7.337 v 47.829 c 0 4.052 3.285 7.337 7.337 7.337" style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(247,211,62); fill-rule: nonzero; opacity: 1;" transform=" matrix(1 0 0 1 0 0) " stroke-linecap="round"/>
                                <path d="M 29.703 55.167 V 7.337 C 29.703 3.285 32.988 0 37.04 0 h -8.669 c -4.052 0 -7.337 3.285 -7.337 7.337 v 47.829 c 0 4.052 3.285 7.337 7.337 7.337 h 8.669 C 32.988 62.504 29.703 59.219 29.703 55.167 z" style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(237,198,36); fill-rule: nonzero; opacity: 1;" transform=" matrix(1 0 0 1 0 0) " stroke-linecap="round"/>
                                <path d="M 56.358 62.491 l -9.951 0.013 l -1.841 -11.575 c -0.495 -3.115 -3.449 -5.259 -6.565 -4.763 c -0.432 0.069 -0.843 0.189 -1.233 0.346 c -0.33 0.941 -1.935 1.809 -1.769 2.858 l 3.257 20.11 c 0.792 4.45 3.371 8.617 6.587 12.481 c 1.49 2.223 1.068 5.008 1.725 8.04 l 10 0 L 56.358 62.491 z" style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(253,218,170); fill-rule: nonzero; opacity: 1;" transform=" matrix(1 0 0 1 0 0) " stroke-linecap="round"/>
                                <path d="M 39.669 69.491 l -3.174 -19.958 c -0.167 -1.049 -0.057 -2.081 0.273 -3.022 c -2.424 0.976 -3.957 3.535 -3.53 6.219 l 2.923 18.381 c 0.729 4.098 2.856 7.859 5.818 11.417 c 1.372 2.047 2.144 4.679 2.75 7.471 h 4.243 c -0.658 -3.032 -1.496 -5.889 -2.986 -8.112 C 42.77 78.024 40.461 73.941 39.669 69.491 z" style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(254,196,120); fill-rule: nonzero; opacity: 1;" transform=" matrix(1 0 0 1 0 0) " stroke-linecap="round"/>
                                <path d="M 38.612 54.644 l 4.703 -0.864 l -0.552 -3.472 c -0.301 -1.893 -2.096 -3.196 -3.989 -2.895 c -0.431 0.069 -0.832 0.215 -1.19 0.422" style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(254,196,120); fill-rule: nonzero; opacity: 1;" transform=" matrix(1 0 0 1 0 0) " stroke-linecap="round"/>
                            </g>
                            </svg>
                    </div>
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full  mb-4" x-show="tabSwitchCount == 2">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" width="256" height="256" viewBox="0 0 256 256" xml:space="preserve">
                        <g style="stroke: none; stroke-width: 0; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: none; fill-rule: nonzero; opacity: 1;" transform="translate(1.4065934065934016 1.4065934065934016) scale(2.81 2.81)">
                            <path d="M 56.35 62.537 l 0.63 -3.584 c 0.575 -3.271 3.722 -5.477 6.993 -4.902 h 0 c 3.271 0.575 5.477 3.722 4.902 6.993 l -1.345 7.647 L 64.085 90 h -7.517 C 53.808 80.129 53.398 70.884 56.35 62.537 z" style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(253,218,170); fill-rule: nonzero; opacity: 1;" transform=" matrix(1 0 0 1 0 0) " stroke-linecap="round"/>
                            <path d="M 54.932 62.504 c 3.739 0 6.771 -3.031 6.771 -6.771 V 6.771 C 61.703 3.031 58.672 0 54.932 0 H 36.04 c -4.052 0 -7.337 3.285 -7.337 7.337 v 47.829 c 0 4.052 3.285 7.337 7.337 7.337" style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(247,62,66); fill-rule: nonzero; opacity: 1;" transform=" matrix(1 0 0 1 0 0) " stroke-linecap="round"/>
                            <path d="M 29.703 55.167 V 7.337 C 29.703 3.285 32.988 0 37.04 0 h -8.669 c -4.052 0 -7.337 3.285 -7.337 7.337 v 47.829 c 0 4.052 3.285 7.337 7.337 7.337 h 8.669 C 32.988 62.504 29.703 59.219 29.703 55.167 z" style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(229,57,61); fill-rule: nonzero; opacity: 1;" transform=" matrix(1 0 0 1 0 0) " stroke-linecap="round"/>
                            <path d="M 56.358 62.491 l -9.951 0.013 l -1.841 -11.575 c -0.495 -3.115 -3.449 -5.259 -6.565 -4.763 c -0.432 0.069 -0.843 0.189 -1.233 0.346 c -0.33 0.941 -1.935 1.809 -1.769 2.858 l 3.257 20.11 c 0.792 4.45 3.371 8.617 6.587 12.481 c 1.49 2.223 1.068 5.008 1.725 8.04 l 10 0 L 56.358 62.491 z" style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(253,218,170); fill-rule: nonzero; opacity: 1;" transform=" matrix(1 0 0 1 0 0) " stroke-linecap="round"/>
                            <path d="M 39.669 69.491 l -3.174 -19.958 c -0.167 -1.049 -0.057 -2.081 0.273 -3.022 c -2.424 0.976 -3.957 3.535 -3.53 6.219 l 2.923 18.381 c 0.729 4.098 2.856 7.859 5.818 11.417 c 1.372 2.047 2.144 4.679 2.75 7.471 h 4.243 c -0.658 -3.032 -1.496 -5.889 -2.986 -8.112 C 42.77 78.024 40.461 73.941 39.669 69.491 z" style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(254,196,120); fill-rule: nonzero; opacity: 1;" transform=" matrix(1 0 0 1 0 0) " stroke-linecap="round"/>
                            <path d="M 38.612 54.644 l 4.703 -0.864 l -0.552 -3.472 c -0.301 -1.893 -2.096 -3.196 -3.989 -2.895 c -0.431 0.069 -0.832 0.215 -1.19 0.422" style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(254,196,120); fill-rule: nonzero; opacity: 1;" transform=" matrix(1 0 0 1 0 0) " stroke-linecap="round"/>
                        </g>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2" x-show="tabSwitchCount == 1">Προειδοποίηση!</h3>
                    <p class="text-gray-600 mb-1">Άλλαξες καρτέλα κατά τη διάρκεια μιας ερώτησης.</p>
                    <div class="text-red-600 font-bold text-lg mb-4">
                        <span x-text="tabSwitchCount"></span> / 2 παραβάσεις
                    </div>
                    <p x-show="tabSwitchCount == 1"
                       class="text-sm text-gray-700 mb-6 bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                        <span class="font-semibold">⚠️ Μία ακόμη παράβαση</span> και το παιχνίδι θα τερματιστεί αυτόματα!
                    </p>
                    <p x-show="tabSwitchCount == 2"
                       class="text-sm text-gray-700 mb-6 bg-red-50 border border-red-200 rounded-lg p-3">
                        <span class="font-semibold"> Το παιχνίδι θα τερματιστεί </span> λόγω υπέρβασης του ορίου αλλαγής καρτέλας.
                    </p>
                    <button @click="closeTabWarning"
                            x-show="tabSwitchCount == 1"
                            class="w-full bg-gradient-to-r from-red-600 to-pink-600 hover:from-red-700 hover:to-pink-700 text-white font-bold py-3 px-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-200">
                        Κατάλαβα
                    </button>
                </div>
            </div>
        </div>

        <div x-show="showForfeitConfirmation"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50"
             @click.self="showForfeitConfirmation = false">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 transform transition-all"
                 @click.stop>
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-orange-100 mb-4">
                        <svg class="h-10 w-10 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Λήξη παιχνιδιού;</h3>
                    <p class="text-gray-600 mb-6">Είσαι σίγουρος ότι θέλεις να εγκαταλείψεις αυτό το παιχνίδι; Ο αντίπαλός σου θα ανακηρυχθεί νικητής.</p>
                    <div class="flex gap-3">
                        <button @click="showForfeitConfirmation = false"
                                class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 px-6 rounded-xl transition-all duration-200">
                            Ακύρωση
                        </button>
                        <button @click="showForfeitConfirmation = false; forfeitGame(false)"
                                class="flex-1 bg-gradient-to-r from-red-600 to-pink-600 hover:from-red-700 hover:to-pink-700 text-white font-bold py-3 px-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-200">
                            Λήξη
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="loading && phase === 'question'"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);">
            <div class="absolute inset-0 bg-black/60"></div>

            <div class="relative bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl shadow-2xl max-w-md w-full p-8 border-2 border-blue-300 transform transition-all">
                <div class="text-center space-y-6">
                    <div class="flex justify-center">
                        <div class="relative">
                            <div class="w-24 h-24 border-8 border-blue-200 rounded-full"></div>
                            <div class="w-24 h-24 border-8 border-blue-600 rounded-full border-t-transparent absolute top-0 left-0 animate-spin"></div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-2xl font-bold text-slate-900 mb-2">Επεξεργασία...</h3>
                        <p class="text-slate-600 font-medium">Η απάντησή σου υποβάλλεται</p>
                    </div>

                    <div class="flex justify-center gap-2">
                        <div class="w-3 h-3 bg-blue-600 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                        <div class="w-3 h-3 bg-indigo-600 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                        <div class="w-3 h-3 bg-purple-600 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="!isMyTurn"
             x-cloak
             class="fixed inset-0 z-30 flex items-center justify-center p-4 pb-32 lg:pb-4"
             style="backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); overflow: scroll">
            <div class="absolute inset-0 bg-black/60"></div>

            <div class="relative bg-gradient-to-br from-indigo-50 to-purple-50 rounded-2xl shadow-2xl max-w-lg w-full p-6 border-2 border-indigo-300 transform transition-all overflow-scroll">
                <div class="space-y-1">
                    <div class="text-center border-b border-indigo-200 pb-4">
                        <h3 class="text-xl lg:text-2xl font-bold text-indigo-900 mb-2 flex items-center justify-center gap-2">
                            <div class="w-3 h-3 bg-indigo-500 rounded-full animate-pulse"></div>
                            <span x-text="opponent?.display_name"></span> παίζει
                            <div class="w-3 h-3 bg-indigo-500 rounded-full animate-pulse"></div>
                        </h3>
                    </div>

                    <div x-show="!opponentMove || opponentMove?.phase === 'category'" class="text-center py-8">
                        <div class="flex flex-col items-center gap-4">
                            <div class="relative w-32 h-32">
                                <div class="absolute inset-0 rounded-full border-4"
                                     :class="opponentInactivityTimeRemaining < 30 ? 'border-red-200' : (opponentInactivityTimeRemaining < 60 ? 'border-amber-200' : 'border-indigo-200')"></div>

                                <div class="absolute inset-0 rounded-full border-4 border-r-transparent border-b-transparent border-l-transparent animate-spin"
                                     :class="opponentInactivityTimeRemaining < 30 ? 'border-t-red-600' : (opponentInactivityTimeRemaining < 60 ? 'border-t-amber-500' : 'border-t-indigo-600')"></div>

                                <div class="absolute inset-0 flex items-center justify-center">
                                    <div class="text-center">
                                        <div class="text-3xl font-black"
                                             :class="opponentInactivityTimeRemaining < 30 ? 'text-red-600 animate-pulse' : (opponentInactivityTimeRemaining < 60 ? 'text-amber-600' : 'text-indigo-600')"
                                             x-text="Math.floor(opponentInactivityTimeRemaining / 60) + ':' + String(opponentInactivityTimeRemaining % 60).padStart(2, '0')"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <p class="text-lg font-bold"
                                   :class="opponentInactivityTimeRemaining < 30 ? 'text-red-700' : (opponentInactivityTimeRemaining < 60 ? 'text-amber-700' : 'text-indigo-900')">
                                    Ο παίκτης επιλέγει κατηγορία...
                                </p>
                                <p class="text-sm text-slate-600">
                                    Παρακαλώ περιμένετε
                                </p>
                            </div>

                            <!-- Progress Bar -->
                            <div class="w-full max-w-xs">
                                <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden shadow-inner">
                                    <div class="h-3 rounded-full transition-all duration-1000 shadow-lg"
                                         :class="opponentInactivityTimeRemaining > 60 ? 'bg-gradient-to-r from-indigo-400 to-indigo-600' : (opponentInactivityTimeRemaining > 30 ? 'bg-gradient-to-r from-amber-400 to-amber-500' : 'bg-gradient-to-r from-red-400 to-red-600')"
                                         :style="`width: ${(opponentInactivityTimeRemaining / 120) * 100}%`"></div>
                                </div>

                                <!-- Urgency Warning Message -->
                                <div x-show="opponentInactivityTimeRemaining < 60"
                                     x-transition:enter="transition ease-out duration-300"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     class="mt-3 text-xs font-bold px-3 py-2 rounded-lg text-center"
                                     :class="opponentInactivityTimeRemaining < 30 ? 'bg-red-100 text-red-800 animate-pulse' : 'bg-amber-100 text-amber-800'">
                                    <span x-show="opponentInactivityTimeRemaining < 30">⚠️ Ο χρόνος τελειώνει!</span>
                                    <span x-show="opponentInactivityTimeRemaining >= 30 && opponentInactivityTimeRemaining < 60">⏰ Ο χρόνος εξαντλείται</span>
                                </div>
                            </div>

                            <!-- Category Icon if selected -->
                            <div x-show="opponentMove?.category" class="inline-flex items-center gap-3 bg-white rounded-xl p-4 shadow-md border border-slate-200 mt-2">
                                <div class="text-4xl" x-text="opponentMove?.category?.icon || '📝'"></div>
                                <div class="text-left">
                                    <div class="text-xs text-slate-500 uppercase tracking-wide font-medium">ΚΑΤΗΓΟΡΙΑ</div>
                                    <div class="text-lg font-bold text-slate-800 mt-1" x-text="opponentMove?.category?.name"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Answering Question Phase -->
                    <div x-show="opponentMove?.phase === 'difficulty'" class="space-y-4">
                        <div class="text-center py-2">
                            <div class="inline-flex items-center gap-2 bg-indigo-100 text-indigo-800 font-bold px-5 py-2.5 rounded-full text-lg">
                                Απαντάει στην ερώτηση...
                            </div>
                        </div>

                        <div class="bg-white rounded-xl p-4 shadow-md border border-slate-200">
                            <div class="flex justify-between text-sm mb-2">
                                <span class="font-semibold text-slate-600">Χρόνος</span>
                                <span class="font-bold text-indigo-600 text-xl" x-text="opponentTimeRemaining + 's'"></span>
                            </div>
                            <div class="w-full bg-slate-200 rounded-full h-3 overflow-hidden">
                                <div class="h-3 rounded-full transition-all duration-1000 shadow-sm"
                                     :class="opponentTimeRemaining > 20 ? 'bg-gradient-to-r from-indigo-400 to-indigo-600' : (opponentTimeRemaining > 10 ? 'bg-gradient-to-r from-amber-400 to-amber-500' : 'bg-gradient-to-r from-rose-400 to-rose-600')"
                                     :style="`width: ${(opponentTimeRemaining / 60) * 100}%`"></div>
                            </div>
                        </div>

                        <div class="flex items-center justify-center gap-3 flex-wrap">
                            <div class="inline-flex items-center gap-2 bg-white rounded-xl px-4 py-2 shadow-md border border-slate-200">
                                <span class="text-slate-600 font-medium text-sm">Κατηγορία:</span>
                                <span class="font-bold text-slate-900" x-text="opponentMove?.category?.name"></span>
                            </div>
                            <div class="inline-flex items-center gap-2 bg-white rounded-xl px-4 py-2 shadow-md border border-slate-200">
                                <span class="text-slate-600 font-medium text-sm">Επίπεδο:</span>
                                <span class="font-bold text-slate-900" x-text="getDifficultyLabel(opponentMove?.difficulty)"></span>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl p-4 shadow-md border border-slate-200">
                            <div class="text-xs text-slate-500 uppercase tracking-wide font-medium mb-2">ΕΡΩΤΗΣΗ</div>
                            <div class="text-slate-900 font-medium leading-relaxed" x-text="opponentMove?.question"></div>
                            <div class="text-slate-400 font-medium text-sm mt-2" x-text="'Δημιουργήθηκε από τον χρήστη: ' + opponentMove?.created_by"></div>
                        </div>

                        <div x-show="opponentMove?.image_url" class="bg-gradient-to-br from-gray-50 to-slate-100 rounded-xl p-3 shadow-md border border-slate-200">
                            <img :src="'/' + opponentMove?.image_url"
                                 style="max-width: 200px;"
                                 alt="Question image"
                                 class="w-full mx-auto rounded-lg shadow-sm border border-gray-300">
                        </div>
                    </div>

                    <div x-show="opponentMove?.phase === 'result'" class="space-y-4">
                        <div class="flex items-center justify-center gap-3 flex-wrap">
                            <div class="inline-flex items-center bg-white rounded-xl px-3 py-2 shadow-md border border-slate-200">
                                <span class="font-bold text-slate-800" x-text="opponentMove?.category?.name"></span>
                            </div>
                            <div class="inline-flex items-center bg-white rounded-xl px-3 py-2 shadow-md border border-slate-200">
                                <span class="font-bold text-slate-800" x-text="getDifficultyLabel(opponentMove?.difficulty)"></span>
                            </div>
                        </div>

                        <div class="py-4">
                            <div x-show="opponentMove?.is_correct"
                                 x-transition:enter="transition ease-out duration-500"
                                 x-transition:enter-start="opacity-0 scale-75 -translate-y-4"
                                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                 class="w-full flex items-center gap-4 bg-gradient-to-r from-emerald-50 to-emerald-100 border-2 border-emerald-400 rounded-2xl px-6 py-5 shadow-xl animate-pulse-slow">
                                <div class="flex-shrink-0">
                                    <div class="w-16 h-16 lg:w-20 lg:h-20 rounded-full bg-emerald-500 flex items-center justify-center shadow-lg transform transition-transform hover:scale-110">
                                        <div class="text-4xl lg:text-5xl text-white font-black">✓</div>
                                    </div>
                                </div>
                                <div class="flex-1 text-left">
                                    <div class="text-emerald-900 font-black text-2xl lg:text-3xl mb-1">Σωστό!</div>
                                    <div class="text-emerald-700 font-bold text-lg lg:text-xl">
                                        +<span x-text="opponentMove?.points_earned"></span> πόντοι
                                    </div>
                                </div>
                            </div>

                            <div x-show="!opponentMove?.is_correct"
                                 x-transition:enter="transition ease-out duration-500"
                                 x-transition:enter-start="opacity-0 scale-75 -translate-y-4"
                                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                 class="w-full flex items-center gap-4 bg-gradient-to-r from-rose-50 to-rose-100 border-2 border-rose-400 rounded-2xl px-6 py-5 shadow-xl">
                                <div class="flex-shrink-0">
                                    <div class="w-16 h-16 lg:w-20 lg:h-20 rounded-full bg-rose-500 flex items-center justify-center shadow-lg transform transition-transform hover:scale-110">
                                        <div class="text-4xl lg:text-5xl text-white font-black">×</div>
                                    </div>
                                </div>
                                <div class="flex-1 text-left">
                                    <div class="text-rose-900 font-black text-2xl lg:text-3xl mb-1">Λάθος</div>
                                    <div class="text-rose-700 font-bold text-lg lg:text-xl">
                                        0 πόντοι
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gradient-to-r from-slate-50 to-gray-50 rounded-xl p-4 border border-slate-200 shadow-sm">
                            <div class="text-xs text-slate-500 uppercase tracking-wide font-medium mb-2">Ερώτηση</div>
                            <div class="text-slate-900 font-medium" x-text="opponentMove?.question"></div>
                            <div class="text-slate-400 font-medium text-sm" x-text="'Δημιουργήθηκε από τον χρήστη: ' + opponentMove?.created_by"></div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="bg-white rounded-xl p-4 shadow-md border border-slate-200">
                                <div class="text-xs text-slate-500 uppercase tracking-wide font-medium mb-2">Απάντηση Αντιπάλου</div>
                                <div class="text-slate-900 font-bold break-words" x-text="opponentMove?.answer || '-'"></div>
                            </div>
                            <div class="bg-white rounded-xl p-4 shadow-md border border-slate-200">
                                <div class="text-xs text-slate-500 uppercase tracking-wide font-medium mb-2">Σωστή Απάντηση</div>
                                <div class="text-emerald-700 font-bold break-words" x-text="opponentMove?.correct_answer"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Move History Panel -->
        <div x-show="showMoveHistory"
             x-cloak
             class="fixed inset-0 z-50 flex items-end lg:items-center lg:justify-center p-0 lg:p-4"
             @click.self="showMoveHistory = false">
            <div class="absolute inset-0 bg-black bg-opacity-50 backdrop-blur-sm"></div>

            <div class="relative bg-white rounded-t-3xl lg:rounded-2xl shadow-2xl w-full lg:max-w-2xl max-h-[85vh] lg:max-h-[80vh] flex flex-col"
                 @click.stop>

                <!-- Header -->
                <div class="flex items-center justify-between p-4 lg:p-6 border-b border-slate-200 bg-gradient-to-r from-indigo-50 to-purple-50">
                    <h3 class="text-xl lg:text-2xl font-bold text-slate-900">Ιστορικό Κινήσεων</h3>
                    <button @click="showMoveHistory = false"
                            class="p-2 hover:bg-white rounded-lg transition-colors">
                        <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Content -->
                <div class="flex-1 overflow-y-auto p-4 lg:p-6">
                    <template x-if="game.rounds && game.rounds.length > 0">
                        <div class="space-y-3">
                            <template x-for="round in game.rounds.slice().reverse()" :key="round.id">
                                <div class="bg-gradient-to-r from-slate-50 to-slate-100 rounded-xl p-4 shadow-sm border border-slate-200">
                                    <!-- Round Header -->
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full text-white font-bold text-sm bg-gradient-to-br from-indigo-500 to-purple-600"
                                                  x-text="round.round_number">
                                            </span>
                                            <span class="font-semibold text-slate-700">Γύρος <span x-text="round.round_number"></span></span>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <span class="text-xs font-semibold px-2 py-1 rounded-lg"
                                                  :class="{
                                                      'bg-emerald-100 text-emerald-700': round.difficulty?.value === 'easy',
                                                      'bg-amber-100 text-amber-700': round.difficulty?.value === 'medium',
                                                      'bg-rose-100 text-rose-700': round.difficulty?.value === 'hard'
                                                  }"
                                                  x-text="round.difficulty?.label">
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Round Details -->
                                    <div class="space-y-2 text-sm">
                                        <div class="flex items-start gap-2">
                                            <span class="text-slate-500 min-w-24">Κατηγορία:</span>
                                            <span class="font-medium text-slate-700 flex items-center gap-1">
                                                <span x-text="round.category?.icon"></span>
                                                <span x-text="round.category?.name"></span>
                                            </span>
                                        </div>

                                        <template x-if="round.question">
                                            <div class="flex items-start gap-2">
                                                <span class="text-slate-500 min-w-24">Ερώτηση:</span>
                                                <span class="font-medium text-slate-700" x-text="round.question.text"></span>
                                            </div>
                                        </template>

                                        <template x-if="round.player_answer">
                                            <div class="flex items-start gap-2">
                                                <span class="text-slate-500 min-w-24">Η απάντησή σου:</span>
                                                <span class="font-semibold"
                                                      :class="round.is_correct ? 'text-green-700' : 'text-red-700'"
                                                      x-text="round.player_answer"></span>
                                            </div>
                                        </template>

                                        <template x-if="round.correct_answer && !round.is_correct">
                                            <div class="flex items-start gap-2">
                                                <span class="text-slate-500 min-w-24">Σωστή:</span>
                                                <span class="font-semibold text-emerald-700" x-text="round.correct_answer"></span>
                                            </div>
                                        </template>

                                        <div class="flex items-center gap-4">
                                            <template x-if="round.points_earned !== undefined">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-slate-500">Πόντοι:</span>
                                                    <span class="font-bold text-lg"
                                                          :class="round.points_earned > 0 ? 'text-green-600' : 'text-red-600'"
                                                          x-text="round.points_earned > 0 ? '+' + round.points_earned : '0'">
                                                    </span>
                                                </div>
                                            </template>

                                            <template x-if="round.time_taken">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-slate-500">Χρόνος:</span>
                                                    <span class="font-medium text-slate-700" x-text="round.time_taken + 's'"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    <template x-if="!game.rounds || game.rounds.length === 0">
                        <div class="text-center py-12">
                            <div class="mx-auto w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mb-4">
                                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <p class="text-slate-600">Δεν υπάρχουν προηγούμενες κινήσεις ακόμα</p>
                        </div>
                    </template>
                </div>

                <!-- Footer -->
                <div class="p-4 lg:p-6 border-t border-slate-200 bg-slate-50">
                    <button @click="showMoveHistory = false"
                            class="w-full bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-bold py-3 px-6 rounded-xl shadow-md hover:shadow-lg transition-all duration-200">
                        Κλείσιμο
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
