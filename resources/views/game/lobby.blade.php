<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            QuizBall - Football Quiz Game
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid md:grid-cols-2 gap-6">
                        <!-- Create Game Card -->
                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-6 rounded-lg shadow-md">
                            <h3 class="text-2xl font-bold text-blue-900 mb-4">⚽ Create New Game</h3>

                            <form method="POST" action="{{ route('game.create') }}" class="space-y-4">
                                @csrf

                                @guest
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Your Name</label>
                                    <input type="text" name="guest_name"
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                           placeholder="Enter your name" required>
                                </div>
                                @endguest

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Game Type</label>
                                    <div class="space-y-2">
                                        <label class="flex items-center p-3 border-2 border-gray-300 rounded-lg cursor-pointer hover:bg-blue-50 transition">
                                            <input type="radio" name="game_type" value="ai" checked class="mr-3">
                                            <div>
                                                <div class="font-semibold">🤖 Play vs AI</div>
                                                <div class="text-sm text-gray-600">Challenge the computer opponent</div>
                                            </div>
                                        </label>

                                        <label class="flex items-center p-3 border-2 border-gray-300 rounded-lg cursor-pointer hover:bg-blue-50 transition">
                                            <input type="radio" name="game_type" value="human" class="mr-3">
                                            <div>
                                                <div class="font-semibold">👥 Play vs Human</div>
                                                <div class="text-sm text-gray-600">Get an invite code to share with a friend</div>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition">
                                    Start Game
                                </button>
                            </form>
                        </div>

                        <!-- Join Game Card -->
                        <div class="bg-gradient-to-br from-green-50 to-green-100 p-6 rounded-lg shadow-md">
                            <h3 class="text-2xl font-bold text-green-900 mb-4">🎮 Join Game</h3>

                            <form method="POST" action="{{ route('game.join') }}" class="space-y-4">
                                @csrf

                                @guest
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Your Name</label>
                                    <input type="text" name="guest_name"
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                                           placeholder="Enter your name" required>
                                </div>
                                @endguest

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Game Code</label>
                                    <input type="text" name="game_code" maxlength="6"
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 uppercase tracking-widest text-center text-2xl font-bold"
                                           placeholder="ABC123" required>
                                </div>

                                @error('game_code')
                                <div class="text-red-600 text-sm">{{ $message }}</div>
                                @enderror

                                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg transition">
                                    Join Game
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Game Info -->
                    <div class="mt-8 bg-gray-50 p-6 rounded-lg">
                        <h4 class="text-lg font-bold text-gray-900 mb-3">How to Play</h4>
                        <div class="grid md:grid-cols-3 gap-4 text-sm text-gray-700">
                            <div>
                                <div class="font-semibold mb-1">📚 Choose Category</div>
                                <div>Select from Premier League, World Cup, and more</div>
                            </div>
                            <div>
                                <div class="font-semibold mb-1">🎯 Pick Difficulty</div>
                                <div>Easy (1pt), Medium (2pts), or Hard (3pts)</div>
                            </div>
                            <div>
                                <div class="font-semibold mb-1">✅ Answer Correctly</div>
                                <div>10 rounds total - highest score wins!</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>