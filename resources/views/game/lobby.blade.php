<x-app-layout>
    <x-slot name="title">QuizBall - Λόμπι Παιχνιδιού</x-slot>
    <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50 py-8">
        <div class="max-w-5xl mx-auto px-6">
            <!-- Page Header -->
            <div class="max-w-4xl mx-auto text-center space-y-8">
                <div class="space-y-8">
                    <h1 class="text-4xl md:text-5xl font-black mt-3 text-gray-900 leading-tight">
                        Νομίζετε ότι ξέρετε <br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600">Ποδόσφαιρο ή Μπάσκετ</span>;
                    </h1>
                    <p class="text-xl md:text-2xl text-gray-600 max-w-2xl mx-auto">
                        Προκαλέστε φίλους ή το AI για να το αποδείξετε.
                    </p>

                    @if(session('error'))
                        <div class="mb-6 bg-red-100 border-2 border-red-400 text-red-700 px-6 py-4 rounded-xl shadow-md max-w-2xl mx-auto">
                            <div class="flex items-center gap-3">
                                <svg class="w-6 h-6 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                <span class="font-semibold">{{ session('error') }}</span>
                            </div>
                        </div>
                    @endif

                    <!-- Quick Stats -->
                    <div class="grid grid-cols-3 gap-4 mb-8 max-w-2xl mx-auto">
                        <div class="bg-white p-4 rounded-xl border-2 border-gray-100 text-center">
                            <div class="text-2xl font-bold text-blue-600">{{ $categoriesCount }}</div>
                            <div class="text-sm text-gray-600">Κατηγορίες</div>
                        </div>
                        <div class="bg-white p-4 rounded-xl border-2 border-gray-100 text-center">
                            <div class="text-2xl font-bold text-purple-600">{{ $questionsCount }}</div>
                            <div class="text-sm text-gray-600">Ερωτήσεις</div>
                        </div>
                        <div class="bg-white p-4 rounded-xl border-2 border-gray-100 text-center">
                            <div class="text-2xl font-bold text-pink-600">60s</div>
                            <div class="text-sm text-gray-600">Για κάθε απάντηση</div>
                        </div>
                    </div>

                </div>
            </div>



            <!-- Game Modes -->
            <div class="grid md:grid-cols-2 gap-6 mb-8 mt-5">
                <!-- Create Game -->
                <div class="bg-white rounded-2xl border-2 border-gray-100 p-8 hover:border-blue-300 transition-all duration-200 shadow-lg hover:shadow-xl relative">
                    <div class="w-14 h-14 flex items-center justify-center text-white text-2xl mb-4" style="position: absolute; right: 5px; top: 5px;">
                        ⚡
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-3">Δημιουργία Παιχνιδιού</h2>
                    <p class="text-gray-600 mb-6">Προκαλέστε έναν φίλο ή το AI</p>

                    <form method="POST" action="{{ route('game.create') }}" class="space-y-4">
                        @csrf

                        @guest
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Το Όνομά σας</label>
                                <input type="text" name="guest_name"
                                       class="w-full rounded-xl border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 px-4 py-3 transition-all duration-200"
                                       placeholder="Εισάγετε το όνομά σας" required>
                            </div>
                        @endguest

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Άθλημα</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="flex items-center justify-center gap-2 p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:bg-blue-50 hover:border-blue-300 transition-all duration-200 has-[:checked]:bg-blue-50 has-[:checked]:border-blue-500">
                                    <input type="radio" name="sport" value="football" checked class="w-5 h-5 text-blue-600">
                                    <span class="font-semibold text-gray-900">Ποδόσφαιρο ⚽</span>
                                </label>
                                <label class="flex items-center justify-center gap-2 p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:bg-blue-50 hover:border-blue-300 transition-all duration-200 has-[:checked]:bg-blue-50 has-[:checked]:border-blue-500">
                                    <input type="radio" name="sport" value="basketball" class="w-5 h-5 text-blue-600">
                                    <span class="font-semibold text-gray-900">Μπάσκετ 🏀</span>
                                </label>
                            </div>
                        </div>

                        <script>
                            function updateAiDifficultyLabel(value) {
                                const labels = {
                                    '1': 'Εύκολο',
                                    '2': 'Μεσαίο',
                                    '3': 'Δύσκολο'
                                };
                                document.getElementById('ai-difficulty-label').textContent = labels[value];
                            }

                            function toggleAiDifficulty(show) {
                                const section = document.getElementById('ai-difficulty-section');
                                if (section) {
                                    section.style.display = show ? 'block' : 'none';
                                }
                            }
                        </script>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Ρυθμός Παιχνιδιού</label>
                            <div class="grid grid-cols-3 gap-3">
                                <label class="relative p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-blue-300 transition-all duration-200 has-[:checked]:bg-gradient-to-r has-[:checked]:from-blue-600 has-[:checked]:to-purple-600 has-[:checked]:border-transparent has-[:checked]:shadow-lg has-[:checked]:text-white">
                                    <input type="radio" name="game_pace" value="4" class="sr-only">
                                    <div class="text-center">
                                        <div class="font-bold">Γρήγορο</div>
                                        <div class="text-xs opacity-80">4 κατηγορίες</div>
                                    </div>
                                </label>
                                <label class="relative p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-blue-300 transition-all duration-200 has-[:checked]:bg-gradient-to-r has-[:checked]:from-blue-600 has-[:checked]:to-purple-600 has-[:checked]:border-transparent has-[:checked]:shadow-lg has-[:checked]:text-white">
                                    <input type="radio" name="game_pace" value="6" checked class="sr-only">
                                    <div class="text-center">
                                        <div class="font-bold">Κανονικό</div>
                                        <div class="text-xs opacity-80">6 κατηγορίες</div>
                                    </div>
                                </label>
                                <label class="relative p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-blue-300 transition-all duration-200 has-[:checked]:bg-gradient-to-r has-[:checked]:from-blue-600 has-[:checked]:to-purple-600 has-[:checked]:border-transparent has-[:checked]:shadow-lg has-[:checked]:text-white">
                                    <input type="radio" name="game_pace" value="8" class="sr-only">
                                    <div class="text-center">
                                        <div class="font-bold">Αργό</div>
                                        <div class="text-xs opacity-80">8 κατηγορίες</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div>
                            <div class="space-y-3">
                                <label class="flex items-center gap-3 p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:bg-blue-50 hover:border-blue-300 transition-all duration-200">
                                    <input type="radio" name="game_type" value="ai" checked class="w-5 h-5 text-blue-600" onchange="toggleAiDifficulty(true)">
                                    <div class="flex-1">
                                        <div class="font-bold text-gray-900">Αντίπαλος AI</div>
                                        <div class="text-sm text-gray-600">Γρήγορο παιχνίδι με τον υπολογιστή</div>
                                    </div>
                                </label>

                                <div id="ai-difficulty-section" class="px-4 py-3 bg-blue-50 rounded-xl border-2 border-blue-200">
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Επίπεδο AI</label>
                                    <div class="px-2 py-3">
                                        <input type="range" name="ai_difficulty" min="1" max="3" step="1" value="2"
                                               class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600">
                                        <div class="flex justify-between text-xs text-gray-600 mt-2">
                                            <span>Εύκολο</span>
                                            <span>Μεσαίο</span>
                                            <span>Δύσκολο</span>
                                        </div>
                                    </div>
                                </div>

                                <label class="flex items-center gap-3 p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:bg-green-50 hover:border-green-300 transition-all duration-200">
                                    <input type="radio" name="game_type" value="matchmaking" class="w-5 h-5 text-green-600" onchange="toggleAiDifficulty(false)">
                                    <div class="flex-1">
                                        <div class="font-bold text-gray-900">Γρήγορο Παιχνίδι</div>
                                        <div class="text-sm text-gray-600">Βρείτε γρήγορα έναν τυχαίο αντίπαλο</div>
                                    </div>
                                </label>

                                <label class="flex items-center gap-3 p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:bg-purple-50 hover:border-purple-300 transition-all duration-200 {{ auth()->guest() ? 'opacity-60' : '' }}"
                                       x-data="{ open: false }">
                                    <input type="radio" name="game_type" value="human" class="w-5 h-5 text-purple-600"
                                           {{ auth()->guest() ? 'disabled' : '' }}
                                           @click="@guest open = true; $event.preventDefault(); @endguest"
                                           onchange="toggleAiDifficulty(false)">
                                    <div class="flex-1">
                                        <div class="font-bold text-gray-900 flex items-center gap-2">
                                            Ιδιωτικό Παιχνίδι
                                            @guest
                                                <a href="{{ route('login') }}" class="text-xs bg-red-100 text-red-700 px-2 py-1 rounded-full hover:bg-red-200 transition-colors">
                                                    Απαιτείται Λογαριασμός
                                                </a>
                                            @else
                                                @if(!auth()->user()->isPremium())
                                                    <span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded-full">2/ημέρα</span>
                                                @else
                                                    <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded-full">Απεριόριστα</span>
                                                @endif
                                            @endguest
                                        </div>
                                        <div class="text-sm text-gray-600">Λάβετε κωδικό για να μοιραστείτε με φίλο</div>
                                    </div>

                                    @guest
                                        <!-- Modal for guest users -->
                                        <div x-show="open"
                                             x-cloak
                                             @click.away="open = false"
                                             class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                                            <div @click.stop class="bg-white rounded-2xl p-8 max-w-md w-full shadow-2xl">
                                                <div class="text-center">
                                                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                                        <svg class="w-8 h-8 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                                        </svg>
                                                    </div>
                                                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Απαιτείται Λογαριασμός</h3>
                                                    <p class="text-gray-600 mb-6">Για να δημιουργήσετε ιδιωτικά παιχνίδια πρέπει να συνδεθείτε ή να εγγραφείτε.</p>

                                                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
                                                        <div class="text-sm font-semibold text-blue-900 mb-2">Δωρεάν Λογαριασμός:</div>
                                                        <ul class="text-sm text-blue-800 space-y-1 text-left">
                                                            <li>✓ 2 ιδιωτικά παιχνίδια ανά ημέρα</li>
                                                            <li>✓ Απεριόριστα AI παιχνίδια</li>
                                                            <li>✓ Στατιστικά & Επιτεύγματα</li>
                                                        </ul>
                                                    </div>

                                                    <div class="flex gap-3">
                                                        <a href="{{ route('login') }}"
                                                           class="flex-1 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-bold py-3 rounded-xl transition-all duration-200 text-center">
                                                            Σύνδεση
                                                        </a>
                                                        <a href="{{ route('register') }}"
                                                           class="flex-1 bg-white border-2 border-gray-300 hover:border-gray-400 text-gray-700 font-bold py-3 rounded-xl transition-all duration-200 text-center">
                                                            Εγγραφή
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endguest
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-bold py-4 rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200">
                            Δημιουργία Παιχνιδιού
                        </button>
                    </form>
                </div>

                <!-- Join Game -->
                <div class="bg-white rounded-2xl border-2 border-gray-100 p-8 hover:border-green-300 transition-all duration-200 shadow-lg hover:shadow-xl relative">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-white text-2xl mb-4" style="position: absolute; right: 5px; top: 5px;">
                        🎯
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-3">Συμμετοχή σε Παιχνίδι</h2>
                    <p class="text-gray-600 mb-6">Εισάγετε κωδικό παιχνιδιού για να συμμετάσχετε με φίλο</p>

                    @guest
                        <!-- Guest Info Banner -->
                        <div class="mb-4 bg-blue-50 border-2 border-blue-200 rounded-xl p-4">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                                <div class="text-sm flex-1">
                                    <div class="font-semibold text-blue-900 mb-1">Περιορισμένη πρόσβαση</div>
                                    <div class="text-blue-700">
                                        Οι επισκέπτες μπορούν να συμμετέχουν μόνο σε AI παιχνίδια.
                                        <a href="{{ route('login') }}" class="font-semibold underline hover:text-blue-900">Συνδεθείτε</a> για ιδιωτικά παιχνίδια!
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        @if(!auth()->user()->isPremium())
                            <!-- Free User Info Banner -->
                            <div class="mb-4 bg-purple-50 border-2 border-purple-200 rounded-xl p-4">
                                <div class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-purple-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                    <div class="text-sm">
                                        <div class="font-semibold text-purple-900 mb-1">Δωρεάν Λογαριασμός</div>
                                        <div class="text-purple-700">2 ιδιωτικά παιχνίδια ανά ημέρα • Κάντε αναβάθμιση για απεριόριστα!</div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endguest

                    <form method="POST" action="{{ route('game.join') }}" class="space-y-4">
                        @csrf

                        @guest
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Το Όνομά σας</label>
                                <input type="text" name="guest_name"
                                       class="w-full rounded-xl border-2 border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-200 px-4 py-3 transition-all duration-200"
                                       placeholder="Εισάγετε το όνομά σας" required>
                            </div>
                        @endguest

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Κωδικός Παιχνιδιού</label>
                            <input type="text" name="game_code" maxlength="6"
                                   class="w-full rounded-xl border-2 border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-200 px-4 py-3 uppercase tracking-[0.3em] text-center text-2xl font-bold transition-all duration-200"
                                   placeholder="ABC123" required>
                        </div>

                        @error('game_code')
                        <div class="text-red-600 text-sm font-medium bg-red-50 p-3 rounded-lg">{{ $message }}</div>
                        @enderror

                        <button type="submit" class="w-full bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-bold py-4 rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200">
                            Συμμετοχή
                        </button>

                        <div class="flex justify-center mt-4">
                            <img src="{{ asset('storage/gifs/crocodile-67.gif') }}" alt="">
                        </div>
                    </form>
                </div>
            </div>

            <!-- Game Rules -->
            <div class="bg-white rounded-2xl border-2 border-gray-100 p-8 shadow-lg" aria-labelledby="quick-rules-title">
                <div class="text-center mb-8">
                    <h3 id="quick-rules-title" class="text-3xl font-bold text-gray-900">Κανόνες</h3>
                    <p class="text-gray-600 mt-2">Απλοί κανόνες για μέγιστη διασκέδαση</p>
                </div>

                <ul class="grid md:grid-cols-4 gap-6 list-none p-0 m-0" role="list">
                    <li class="flex flex-col items-center text-center group" role="listitem">
                        <div class="w-16 h-16 bg-gradient-to-br from-blue-100 to-blue-200 rounded-2xl flex items-center justify-center text-3xl mb-4 group-hover:scale-110 transition-transform duration-200">
                            📝
                        </div>
                        <div class="font-bold text-gray-900 mb-2">Επιλέξτε Κατηγορία</div>
                        <div class="text-sm text-gray-600 leading-relaxed">Από Premier League μέχρι Παγκόσμιο Κύπελλο</div>
                    </li>

                    <li class="flex flex-col items-center text-center group" role="listitem">
                        <div class="w-16 h-16 bg-gradient-to-br from-purple-100 to-purple-200 rounded-2xl flex items-center justify-center text-3xl mb-4 group-hover:scale-110 transition-transform duration-200">
                            🎚️
                        </div>
                        <div class="font-bold text-gray-900 mb-2">Επιλέξτε Επίπεδο</div>
                        <div class="text-sm text-gray-600 leading-relaxed">Εύκολο, Μεσαίο ή Δύσκολο</div>
                    </li>

                    <li class="flex flex-col items-center text-center group" role="listitem">
                        <div class="w-16 h-16 bg-gradient-to-br from-pink-100 to-pink-200 rounded-2xl flex items-center justify-center text-3xl mb-4 group-hover:scale-110 transition-transform duration-200">
                            ⏱️
                        </div>
                        <div class="font-bold text-gray-900 mb-2">Νικήστε το Χρόνο</div>
                        <div class="text-sm text-gray-600 leading-relaxed">60 δευτερόλεπτα ανά ερώτηση</div>
                    </li>

                    <li class="flex flex-col items-center text-center group" role="listitem">
                        <div class="w-16 h-16 bg-gradient-to-br from-amber-100 to-amber-200 rounded-2xl flex items-center justify-center text-3xl mb-4 group-hover:scale-110 transition-transform duration-200">
                            🏆
                        </div>
                        <div class="font-bold text-gray-900 mb-2">Κερδίστε Πόντους</div>
                        <div class="text-sm text-gray-600 leading-relaxed">Ο υψηλότερος βαθμός κερδίζει</div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
