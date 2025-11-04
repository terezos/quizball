<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>QuizBall - Παιχνίδι Ποδοσφαιρικής Γνώσης</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
{{--<body class="bg-gradient-to-br from-indigo-50 via-white to-purple-50 min-h-screen">--}}
<body class="font-sans antialiased">
<div class="flex flex-col min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50">
@include('layouts.navigation')
    <!-- Header -->
{{--    <header class="sticky top-0 z-50 backdrop-blur-sm bg-white border-b border-gray-200">--}}
{{--        <div class="container mx-auto px-6 py-2">--}}
{{--            <div class="flex justify-between items-center">--}}
{{--                <a href="/" class="text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 transition-all duration-200">--}}
{{--                    quizball.io--}}
{{--                </a>--}}
{{--                @if (Route::has('login'))--}}
{{--                            <div class="flex gap-3">--}}
{{--                                @auth--}}
{{--                                    <a href="{{ url('/dashboard') }}" class="px-6 py-2.5 bg-white border-2 border-gray-200 hover:border-blue-500 hover:bg-blue-50 text-gray-700 font-semibold rounded-xl transition-all duration-200 shadow-sm hover:shadow-md inline-flex items-center">--}}
{{--                                        <i class="fa-solid fa-gauge mr-2" aria-hidden="true"></i>--}}
{{--                                    </a>--}}
{{--                                @else--}}
{{--                                    <a href="{{ route('login') }}" class="px-6 py-2.5 bg-white border-2 border-gray-200 hover:border-blue-500 hover:bg-blue-50 text-gray-700 font-semibold rounded-xl transition-all duration-200 shadow-sm hover:shadow-md inline-flex items-center">--}}
{{--                                        <i class="fa-solid fa-right-to-bracket mr-2" aria-hidden="true"></i>--}}
{{--                                    </a>--}}
{{--                                    @if (Route::has('register'))--}}
{{--                                        <a href="{{ route('register') }}" class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105 inline-flex items-center">--}}
{{--                                            <i class="fa-solid fa-user-plus mr-2" aria-hidden="true"></i>--}}
{{--                                        </a>--}}
{{--                                    @endif--}}
{{--                                @endauth--}}
{{--                            </div>--}}
{{--                        @endif--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </header>--}}

    <!-- Hero Section -->
    <main class="container mx-auto px-6 py-12">
        <div class="max-w-4xl mx-auto text-center space-y-8">
            <!-- Main Headline -->
            <div class="space-y-8">
                <h1 class="text-4xl md:text-5xl font-black mt-3 text-gray-900 leading-tight">
                    Νομίζετε ότι ξέρετε <br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600">Ποδόσφαιρο ή Μπάσκετ</span>;
                </h1>
                <p class="text-xl md:text-2xl text-gray-600 max-w-2xl mx-auto">
                    Προκαλέστε φίλους για να το αποδείξετε.
                </p>
            </div>

            <!-- CTA Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center pt-4">
                <a href="{{ route('game.lobby') }}" class="px-8 py-4 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white text-lg font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:scale-105 transition-all duration-200">
                    <i class="fa-solid fa-meteor"></i> Ξεκινήστε να Παίζετε
                </a>
            </div>

            <!-- Features Grid -->
            <div class="grid md:grid-cols-3 gap-6 pt-16">
                <div class="bg-white p-6 rounded-2xl border-2 border-gray-100 hover:border-blue-300 transition-all duration-200">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-500 rounded-xl flex items-center justify-center text-white text-2xl font-bold mb-4">
                        1
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Επιλέξτε τη Μάχη σας</h3>
                    <p class="text-gray-600">
                        Παίξτε εναντίον AI, με έναν άγνωστο ή προκαλέστε έναν φίλο με κωδικό παιχνιδιού
                    </p>
                </div>

                <div class="bg-white p-6 rounded-2xl border-2 border-gray-100 hover:border-blue-300 transition-all duration-200">
                    <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-500 rounded-xl flex items-center justify-center text-white text-2xl font-bold mb-4">
                        2
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Επιλέξτε Σοφά</h3>
                    <p class="text-gray-600">
                        8 κατηγορίες, 3 επίπεδα - Εύκολο (1π), Μεσαίο (2π), Δύσκολο (3π)
                    </p>
                </div>

                <div class="bg-white p-6 rounded-2xl border-2 border-gray-100 hover:border-blue-300 transition-all duration-200">
                    <div class="w-12 h-12 bg-gradient-to-br from-pink-500 to-red-500 rounded-xl flex items-center justify-center text-white text-2xl font-bold mb-4">
                        3
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Κερδίστε Δόξα</h3>
                    <p class="text-gray-600">
                        Απαντήστε σε όλες τις ερωτήσεις ή ξεπεράστε τον αντίπαλό σας για να διεκδικήσετε τη νίκη
                    </p>
                </div>
            </div>

            <!-- How It Works -->
            <div id="how-it-works" class="pt-16 space-y-6">
                <h2 class="text-3xl font-bold text-gray-900">Απλό, Γρήγορο, Διασκεδαστικό</h2>
                <div class="bg-gradient-to-br from-blue-50 to-purple-50 p-8 rounded-2xl border-2 border-blue-200 max-w-2xl mx-auto text-left">
                    <ul class="space-y-4">
                        <li class="flex items-start gap-3">
                            <span class="w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center flex-shrink-0 font-bold text-sm">✓</span>
                            <span class="text-gray-700"><strong>60 δευτερόλεπτα</strong> για να επιλέξετε κατηγορία/επίπεδο</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center flex-shrink-0 font-bold text-sm">✓</span>
                            <span class="text-gray-700"><strong>60 δευτερόλεπτα</strong> για να απαντήσετε σε κάθε ερώτηση</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center flex-shrink-0 font-bold text-sm">✓</span>
                            <span class="text-gray-700"><strong>Χωρίς χάσιμο χρόνου</strong> - Το παιχνίδι τελειώνει όταν απαντηθούν όλες οι ερωτήσεις ή όταν κάποιο παραιτηθεί</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center flex-shrink-0 font-bold text-sm">✓</span>
                            <span class="text-gray-700"><strong>Ενημερώσεις σε πραγματικό χρόνο</strong> - Παρακολουθήστε τις κινήσεις του αντιπάλου σας ζωντανά</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </main>

    <x-footer />

</div>
</body>
</html>
