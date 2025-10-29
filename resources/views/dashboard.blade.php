<x-app-layout>
    <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50 py-8">
        <div class="max-w-5xl mx-auto px-6">
            <!-- Page Header -->
            <div class="text-center mb-10">
                <h1 class="text-4xl font-black text-gray-900 mb-3">
                    Ready to <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600">Play</span>?
                </h1>
                <p class="text-lg text-gray-600">Choose your game mode and let's get started</p>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-3 gap-4 mb-8 max-w-2xl mx-auto">
                <div class="bg-white p-4 rounded-xl border-2 border-gray-100 text-center">
                    <div class="text-2xl font-bold text-blue-600">8</div>
                    <div class="text-sm text-gray-600">Categories</div>
                </div>
                <div class="bg-white p-4 rounded-xl border-2 border-gray-100 text-center">
                    <div class="text-2xl font-bold text-purple-600">24</div>
                    <div class="text-sm text-gray-600">Questions</div>
                </div>
                <div class="bg-white p-4 rounded-xl border-2 border-gray-100 text-center">
                    <div class="text-2xl font-bold text-pink-600">60s</div>
                    <div class="text-sm text-gray-600">Per Answer</div>
                </div>
            </div>

            <!-- Game Modes -->
            <div class="grid md:grid-cols-2 gap-6 mb-8">
                <!-- Create Game -->
                <div class="bg-white rounded-2xl border-2 border-gray-100 p-8 hover:border-blue-300 transition-all duration-200 shadow-lg hover:shadow-xl">
                    <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-purple-500 rounded-2xl flex items-center justify-center text-white text-2xl mb-4">
                        ⚡
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-3">Create Game</h2>
                    <p class="text-gray-600 mb-6">Start a new game and challenge a friend or AI</p>

                    <form method="POST" action="{{ route('game.create') }}" class="space-y-4">
                        @csrf

                        @guest
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Your Name</label>
                            <input type="text" name="guest_name" 
                                   class="w-full rounded-xl border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 px-4 py-3 transition-all duration-200"
                                   placeholder="Enter your name" required>
                        </div>
                        @endguest

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">Game Type</label>
                            <div class="space-y-3">
                                <label class="flex items-center gap-3 p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:bg-blue-50 hover:border-blue-300 transition-all duration-200">
                                    <input type="radio" name="game_type" value="ai" checked class="w-5 h-5 text-blue-600">
                                    <div class="flex-1">
                                        <div class="font-bold text-gray-900">AI Opponent</div>
                                        <div class="text-sm text-gray-600">Quick match against computer</div>
                                    </div>
                                </label>

                                <label class="flex items-center gap-3 p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:bg-green-50 hover:border-green-300 transition-all duration-200">
                                    <input type="radio" name="game_type" value="matchmaking" class="w-5 h-5 text-green-600">
                                    <div class="flex-1">
                                        <div class="font-bold text-gray-900">Quick Match</div>
                                        <div class="text-sm text-gray-600">Find a random opponent fast</div>
                                    </div>
                                </label>

                                <label class="flex items-center gap-3 p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:bg-purple-50 hover:border-purple-300 transition-all duration-200">
                                    <input type="radio" name="game_type" value="human" class="w-5 h-5 text-purple-600">
                                    <div class="flex-1">
                                        <div class="font-bold text-gray-900">Private Match</div>
                                        <div class="text-sm text-gray-600">Get code to share with friend</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-bold py-4 rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200">
                            Create Game
                        </button>
                    </form>
                </div>

                <!-- Join Game -->
                <div class="bg-white rounded-2xl border-2 border-gray-100 p-8 hover:border-green-300 transition-all duration-200 shadow-lg hover:shadow-xl">
                    <div class="w-14 h-14 bg-gradient-to-br from-green-500 to-emerald-500 rounded-2xl flex items-center justify-center text-white text-2xl mb-4">
                        🎯
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-3">Join Game</h2>
                    <p class="text-gray-600 mb-6">Enter a game code to join your friend</p>

                    <form method="POST" action="{{ route('game.join') }}" class="space-y-4">
                        @csrf

                        @guest
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Your Name</label>
                            <input type="text" name="guest_name"
                                   class="w-full rounded-xl border-2 border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-200 px-4 py-3 transition-all duration-200"
                                   placeholder="Enter your name" required>
                        </div>
                        @endguest

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Game Code</label>
                            <input type="text" name="game_code" maxlength="6"
                                   class="w-full rounded-xl border-2 border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-200 px-4 py-3 uppercase tracking-[0.3em] text-center text-2xl font-bold transition-all duration-200"
                                   placeholder="ABC123" required>
                        </div>

                        @error('game_code')
                        <div class="text-red-600 text-sm font-medium bg-red-50 p-3 rounded-lg">{{ $message }}</div>
                        @enderror

                        <button type="submit" class="w-full bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-bold py-4 rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200">
                            Join Game
                        </button>
                    </form>
                </div>
            </div>

            <!-- Game Rules -->
            <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-2xl border-2 border-amber-200 p-8">
                <h3 class="text-2xl font-bold text-gray-900 mb-6 text-center">Quick Rules</h3>
                <div class="grid md:grid-cols-4 gap-6 text-center">
                    <div>
                        <div class="text-3xl mb-2">📝</div>
                        <div class="font-bold text-gray-900 mb-1">Pick Category</div>
                        <div class="text-sm text-gray-600">Premier League to World Cup</div>
                    </div>
                    <div>
                        <div class="text-3xl mb-2">🎚️</div>
                        <div class="font-bold text-gray-900 mb-1">Choose Level</div>
                        <div class="text-sm text-gray-600">Easy, Medium, or Hard</div>
                    </div>
                    <div>
                        <div class="text-3xl mb-2">⏱️</div>
                        <div class="font-bold text-gray-900 mb-1">Beat The Clock</div>
                        <div class="text-sm text-gray-600">60 seconds per question</div>
                    </div>
                    <div>
                        <div class="text-3xl mb-2">🏆</div>
                        <div class="font-bold text-gray-900 mb-1">Win Points</div>
                        <div class="text-sm text-gray-600">Highest score takes it</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
