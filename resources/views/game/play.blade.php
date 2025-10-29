<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                QuizBall Game - Round {{ $game->current_round }}/{{ $game->max_rounds }}
            </h2>
        </div>
    </x-slot>

    <script>
        window.gameBoard = function(initialGame, player, categories, isMyTurn) {
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
                answer: '',
                topFiveAnswers: ['', '', '', '', ''],
                timeRemaining: 60,
                timer: null,
                lastResult: null,
                pollingInterval: null,

                init() {
                    this.updatePlayers();
                    this.startPolling();

                    if (!this.isMyTurn) {
                        this.phase = 'waiting';
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

                        if (response.ok) {
                            this.phase = 'difficulty';
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
                            this.startTimer();
                        }
                    } finally {
                        this.loading = false;
                    }
                },

                async submitAnswer() {
                    if (!this.canSubmit || this.loading) return;

                    this.stopTimer();
                    this.loading = true;

                    try {
                        let answerData = this.answer;
                        if (this.currentQuestion.type === 'top_5') {
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
                        }, 3000);
                    } finally {
                        this.loading = false;
                    }
                },

                startTimer() {
                    this.timeRemaining = 60;
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

                resetForNextRound() {
                    this.answer = '';
                    this.topFiveAnswers = ['', '', '', '', ''];
                    this.currentQuestion = null;
                    this.lastResult = null;
                    this.phase = 'category';
                    this.isMyTurn = false;
                },

                async startPolling() {
                    this.pollingInterval = setInterval(async () => {
                        try {
                            const response = await fetch(`/game/${this.game.id}/state`);
                            const data = await response.json();

                            this.game = data;
                            this.updatePlayers();
                            this.isMyTurn = data.current_turn_player_id === this.player.id;

                            if (this.isMyTurn && this.phase === 'waiting') {
                                this.phase = 'category';
                            }

                            if (data.status === 'completed') {
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
                }
            }
        }
    </script>

    <div class="py-6" x-data="gameBoard(@js($game), @js($player), @js($categories), {{ $isPlayerTurn ? 'true' : 'false' }})">
        <button @click="forfeitGame"
                class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition">
            🏳️ Forfeit Game
        </button>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Score Display -->
            <div class="bg-white rounded-lg shadow-md p-4 mb-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="text-center p-4 rounded-lg" :class="game.current_turn_player_id === {{ $player->id }} ? 'bg-green-100 ring-2 ring-green-500' : 'bg-gray-50'">
                        <div class="text-sm font-medium text-gray-600">You</div>
                        <div class="text-3xl font-bold text-gray-900" x-text="players[{{ $player->id }}]?.score || 0"></div>
                        <div class="text-xs text-gray-500" x-text="players[{{ $player->id }}]?.display_name"></div>
                    </div>
                    <div class="text-center p-4 rounded-lg" :class="game.current_turn_player_id !== {{ $player->id }} ? 'bg-blue-100 ring-2 ring-blue-500' : 'bg-gray-50'" x-show="opponent">
                        <div class="text-sm font-medium text-gray-600">Opponent</div>
                        <div class="text-3xl font-bold text-gray-900" x-text="opponent?.score || 0"></div>
                        <div class="text-xs text-gray-500" x-text="opponent?.display_name"></div>
                    </div>
                </div>
            </div>

            <!-- Turn Indicator -->
            <div class="bg-white rounded-lg shadow-md p-4 mb-4 text-center">
                <div x-show="isMyTurn" class="text-green-600 font-bold text-lg">
                    🎯 Your Turn! Round <span x-text="game.current_round"></span>/<span x-text="game.max_rounds"></span>
                </div>
                <div x-show="!isMyTurn" class="text-blue-600 font-bold text-lg">
                    ⏳ Waiting for opponent... Round <span x-text="game.current_round"></span>/<span x-text="game.max_rounds"></span>
                </div>
            </div>

            <!-- Game Phase Display -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <!-- Phase 1: Category Selection -->
                <div x-show="phase === 'category'" x-cloak>
                    <h3 class="text-xl font-bold text-gray-900 mb-4 text-center">Choose a Category</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <template x-for="category in categories" :key="category.id">
                            <button @click="selectCategory(category.id)"
                                    :disabled="!isMyTurn || loading"
                                    class="p-6 border-2 border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition disabled:opacity-50 disabled:cursor-not-allowed">
                                <div class="text-4xl mb-2" x-text="category.icon"></div>
                                <div class="font-semibold text-sm" x-text="category.name"></div>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Phase 2: Difficulty Selection -->
                <div x-show="phase === 'difficulty'" x-cloak>
                    <h3 class="text-xl font-bold text-gray-900 mb-4 text-center">Choose Difficulty</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 max-w-3xl mx-auto">
                        <button @click="selectDifficulty('easy')"
                                :disabled="!isMyTurn || loading"
                                class="p-8 border-2 border-green-300 bg-green-50 rounded-lg hover:border-green-500 hover:bg-green-100 transition disabled:opacity-50">
                            <div class="text-3xl mb-2">😊</div>
                            <div class="font-bold text-lg">Easy</div>
                            <div class="text-sm text-gray-600">1 Point</div>
                        </button>
                        <button @click="selectDifficulty('medium')"
                                :disabled="!isMyTurn || loading"
                                class="p-8 border-2 border-yellow-300 bg-yellow-50 rounded-lg hover:border-yellow-500 hover:bg-yellow-100 transition disabled:opacity-50">
                            <div class="text-3xl mb-2">😐</div>
                            <div class="font-bold text-lg">Medium</div>
                            <div class="text-sm text-gray-600">2 Points</div>
                        </button>
                        <button @click="selectDifficulty('hard')"
                                :disabled="!isMyTurn || loading"
                                class="p-8 border-2 border-red-300 bg-red-50 rounded-lg hover:border-red-500 hover:bg-red-100 transition disabled:opacity-50">
                            <div class="text-3xl mb-2">😰</div>
                            <div class="font-bold text-lg">Hard</div>
                            <div class="text-sm text-gray-600">3 Points</div>
                        </button>
                    </div>
                </div>

                <!-- Phase 3: Question & Answer -->
                <div x-show="phase === 'question'" x-cloak>
                    <div class="mb-6">
                        <!-- Timer -->
                        <div class="mb-4">
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-medium">Time Remaining</span>
                                <span class="font-bold" x-text="timeRemaining + 's'"></span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-blue-600 h-3 rounded-full transition-all duration-1000"
                                     :style="`width: ${(timeRemaining / 60) * 100}%`"></div>
                            </div>
                        </div>

                        <h3 class="text-2xl font-bold text-gray-900 mb-4" x-text="currentQuestion.text"></h3>

                        <!-- Text Input Answer -->
                        <div x-show="currentQuestion.type === 'text_input'" class="space-y-4">
                            <input type="text" x-model="answer"
                                   @keydown.enter="submitAnswer"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-lg p-3"
                                   placeholder="Type your answer...">
                        </div>

                        <!-- Multiple Choice Answer -->
                        <div x-show="currentQuestion.type === 'multiple_choice'" class="space-y-3">
                            <template x-for="(ans, index) in currentQuestion.answers" :key="ans.id">
                                <button @click="answer = ans.id"
                                        :class="answer === ans.id ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-500' : 'border-gray-300'"
                                        class="w-full p-4 border-2 rounded-lg hover:border-blue-300 transition text-left">
                                    <span class="font-bold mr-2" x-text="String.fromCharCode(65 + index) + '.'"></span>
                                    <span x-text="ans.text"></span>
                                </button>
                            </template>
                        </div>

                        <!-- Top 5 Answer -->
                        <div x-show="currentQuestion.type === 'top_5'" class="space-y-3">
                            <p class="text-sm text-gray-600 mb-3">Enter 5 correct answers:</p>
                            <template x-for="i in [0,1,2,3,4]" :key="i">
                                <input type="text" x-model="topFiveAnswers[i]"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2"
                                       :placeholder="'Answer ' + (i + 1)">
                            </template>
                        </div>
                    </div>

                    <button @click="submitAnswer"
                            :disabled="!canSubmit || loading"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-6 rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed">
                        Submit Answer
                    </button>
                </div>

                <!-- Phase 4: Result Display -->
                <div x-show="phase === 'result'" x-cloak>
                    <div class="text-center py-8">
                        <div x-show="lastResult.is_correct" class="text-green-600">
                            <div class="text-6xl mb-4">✅</div>
                            <div class="text-2xl font-bold mb-2">Correct!</div>
                            <div class="text-lg">+<span x-text="lastResult.points_earned"></span> points</div>
                        </div>
                        <div x-show="!lastResult.is_correct" class="text-red-600">
                            <div class="text-6xl mb-4">❌</div>
                            <div class="text-2xl font-bold mb-2">Incorrect</div>
                            <div class="text-lg">0 points</div>
                        </div>
                    </div>
                </div>

                <!-- Loading State -->
                <div x-show="loading" class="text-center py-8">
                    <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-blue-600 mx-auto"></div>
                    <p class="mt-4 text-gray-600">Processing...</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
