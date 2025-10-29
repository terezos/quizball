<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>QuizBall - Football Trivia Game</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-indigo-50 via-white to-purple-50 min-h-screen">
    <!-- Header -->
    <header class="container mx-auto px-6 py-6">
        <div class="flex justify-between items-center">
            <div class="text-2xl font-black text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600">
                QuizBall
            </div>
            @if (Route::has('login'))
                <div class="flex gap-3">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="px-5 py-2.5 bg-white border-2 border-gray-200 hover:border-blue-500 text-gray-700 font-semibold rounded-xl transition-all duration-200">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-5 py-2.5 bg-white border-2 border-gray-200 hover:border-blue-500 text-gray-700 font-semibold rounded-xl transition-all duration-200">
                            Login
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl">
                                Sign Up
                            </a>
                        @endif
                    @endauth
                </div>
            @endif
        </div>
    </header>

    <!-- Hero Section -->
    <main class="container mx-auto px-6 py-12">
        <div class="max-w-4xl mx-auto text-center space-y-8">
            <!-- Main Headline -->
            <div class="space-y-4">
                <div class="inline-block px-4 py-2 bg-blue-100 text-blue-700 font-semibold rounded-full text-sm">
                    Test Your Football Knowledge
                </div>
                <h1 class="text-5xl md:text-7xl font-black text-gray-900 leading-tight">
                    Think You Know
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600">Football</span>?
                </h1>
                <p class="text-xl md:text-2xl text-gray-600 max-w-2xl mx-auto">
                    Challenge friends or AI. Pick categories. Answer questions. Win bragging rights.
                </p>
            </div>

            <!-- CTA Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center pt-4">
                <a href="{{ route('game.lobby') }}" class="px-8 py-4 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white text-lg font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:scale-105 transition-all duration-200">
                    Start Playing
                </a>
                <a href="#how-it-works" class="px-8 py-4 bg-white border-2 border-gray-200 hover:border-blue-500 text-gray-700 text-lg font-bold rounded-2xl transition-all duration-200">
                    How It Works
                </a>
            </div>

            <!-- Features Grid -->
            <div class="grid md:grid-cols-3 gap-6 pt-16">
                <div class="bg-white p-6 rounded-2xl border-2 border-gray-100 hover:border-blue-300 transition-all duration-200">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-500 rounded-xl flex items-center justify-center text-white text-2xl font-bold mb-4">
                        1
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Pick Your Battle</h3>
                    <p class="text-gray-600">
                        Play against AI or challenge a friend with a game code
                    </p>
                </div>

                <div class="bg-white p-6 rounded-2xl border-2 border-gray-100 hover:border-blue-300 transition-all duration-200">
                    <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-500 rounded-xl flex items-center justify-center text-white text-2xl font-bold mb-4">
                        2
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Choose Wisely</h3>
                    <p class="text-gray-600">
                        8 categories, 3 difficulties - Easy (1pt), Medium (2pts), Hard (3pts)
                    </p>
                </div>

                <div class="bg-white p-6 rounded-2xl border-2 border-gray-100 hover:border-blue-300 transition-all duration-200">
                    <div class="w-12 h-12 bg-gradient-to-br from-pink-500 to-red-500 rounded-xl flex items-center justify-center text-white text-2xl font-bold mb-4">
                        3
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Win Glory</h3>
                    <p class="text-gray-600">
                        Answer all questions or outscore your opponent to claim victory
                    </p>
                </div>
            </div>

            <!-- How It Works -->
            <div id="how-it-works" class="pt-16 space-y-6">
                <h2 class="text-3xl font-bold text-gray-900">Simple, Fast, Fun</h2>
                <div class="bg-gradient-to-br from-blue-50 to-purple-50 p-8 rounded-2xl border-2 border-blue-200 max-w-2xl mx-auto text-left">
                    <ul class="space-y-4">
                        <li class="flex items-start gap-3">
                            <span class="w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center flex-shrink-0 font-bold text-sm">✓</span>
                            <span class="text-gray-700"><strong>60 seconds</strong> to answer each question</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center flex-shrink-0 font-bold text-sm">✓</span>
                            <span class="text-gray-700"><strong>24 total combinations</strong> - once picked, can't be used again</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center flex-shrink-0 font-bold text-sm">✓</span>
                            <span class="text-gray-700"><strong>No time wasted</strong> - Game ends when all questions answered</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center flex-shrink-0 font-bold text-sm">✓</span>
                            <span class="text-gray-700"><strong>Real-time updates</strong> - Watch your opponent's moves live</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="container mx-auto px-6 py-8 mt-16 border-t border-gray-200">
        <div class="text-center text-gray-600 text-sm">
            <p>© 2025 QuizBall. Made for football fans who think they know it all.</p>
        </div>
    </footer>
</body>
</html>
