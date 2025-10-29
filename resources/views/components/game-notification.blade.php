<div x-data="gameNotification()"
     x-show="showNotification"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-y-4"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 translate-y-4"
     class="fixed bottom-4 right-4 z-50 max-w-md"
     style="display: none;">

    <div class="bg-white rounded-lg shadow-lg border-2 border-blue-500 p-4">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="ml-3 flex-1">
                <h3 class="text-sm font-medium text-gray-900">Active Game in Progress</h3>
                <div class="mt-2 text-sm text-gray-600">
                    <p>You have an active game waiting for you!</p>
                    <div class="mt-2 space-y-1">
                        <p class="text-xs" x-show="gameData">
                            <span class="font-semibold">Round:</span>
                            <span x-text="gameData ? (gameData.currentRound + '/' + gameData.maxRounds) : ''"></span>
                        </p>
                        <p class="text-xs" x-show="gameData && gameData.isYourTurn">
                            <span class="font-semibold text-green-600">It's your turn!</span>
                        </p>
                        <p class="text-xs" x-show="gameData">
                            <span class="font-semibold">Time remaining:</span>
                            <span x-text="formatRemainingTime()" :class="remainingSeconds < 60 ? 'text-red-600 font-bold' : remainingSeconds < 300 ? 'text-orange-600' : 'text-gray-900'"></span>
                        </p>
                    </div>
                </div>
                <div class="mt-4 flex space-x-2">
                    <a :href="gameData?.gameUrl || '#'"
                       x-show="gameData"
                       class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Return to Game
                    </a>
                    <button @click="forfeitGame()"
                            class="inline-flex items-center px-3 py-2 border  text-sm leading-4 font-medium rounded-md border-red-300 hover:border-red-500 text-red-600 hover:text-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Forfeit Game
                    </button>
                </div>
            </div>
            <div class="ml-4 flex-shrink-0">
                <button @click="dismissNotification()"
                        class="inline-flex text-gray-400 hover:text-gray-500 focus:outline-none">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function gameNotification() {
    return {
        showNotification: false,
        gameData: null,
        dismissed: false,
        checkInterval: null,
        countdownInterval: null,
        remainingSeconds: 0,
        fetchedAt: null,

        init() {
            const currentPath = window.location.pathname;
            if (currentPath.includes('/game/') && (currentPath.includes('/play') || currentPath.includes('/wait') || currentPath.includes('/matchmaking'))) {
                return;
            }

            this.checkForActiveGame();
            this.checkInterval = setInterval(() => {
                this.checkForActiveGame();
            }, 30000);

            this.countdownInterval = setInterval(() => {
                this.updateCountdown();
            }, 1000);
        },

        async checkForActiveGame() {
            if (this.dismissed) {
                return;
            }

            try {
                const response = await fetch('/game/check-active', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (data.hasActiveGame && !this.dismissed) {
                    this.gameData = data;
                    this.remainingSeconds = data.remainingSeconds;
                    this.fetchedAt = Date.now();
                    this.showNotification = true;
                } else {
                    this.showNotification = false;
                }
            } catch (error) {
                console.error('Error checking for active game:', error);
            }
        },

        updateCountdown() {
            if (!this.gameData || !this.fetchedAt) {
                return;
            }

            const elapsedSinceLastFetch = Math.floor((Date.now() - this.fetchedAt) / 1000);
            this.remainingSeconds = Math.max(0, this.gameData.remainingSeconds - elapsedSinceLastFetch).toFixed(2);

            if (this.remainingSeconds == 0 || this.remainingSeconds < 0) {
                this.autoForfeitGame();
            }
        },

        dismissNotification() {
            this.showNotification = false;
            this.dismissed = true;
            if (this.checkInterval) {
                clearInterval(this.checkInterval);
            }
            if (this.countdownInterval) {
                clearInterval(this.countdownInterval);
            }
        },

        async autoForfeitGame() {
            if (!this.gameData) {
                return;
            }

            try {
                await fetch(`/game/${this.gameData.gameId}/forfeit`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                this.dismissNotification();
                alert('Your game has been automatically forfeited due to inactivity.');
                window.location.href = '/game';
            } catch (error) {
                console.error('Error auto-forfeiting game:', error);
            }
        },
        async forfeitGame() {
            if (!this.gameData || !confirm('Are you sure you want to forfeit this game?')) {
                return;
            }

            try {
                const response = await fetch(`/game/${this.gameData.gameId}/forfeit`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (response.ok) {
                    this.dismissNotification();
                    window.location.href = '/game';
                }
            } catch (error) {
                console.error('Error forfeiting game:', error);
                alert('Failed to forfeit game. Please try again.');
            }
        },

        formatRemainingTime() {
            if (!this.remainingSeconds && this.remainingSeconds !== 0) return '';

            return `${this.remainingSeconds}s`;
        }
    }
}
</script>
