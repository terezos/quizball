<x-app-layout>
    <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50 py-8">
        <div class="max-w-5xl mx-auto px-6">
            <!-- Page Header -->
            <div class="text-center mb-10">
                <h1 class="text-4xl font-black text-gray-900 mb-3">
                    Έτοιμοι να <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600">Παίξετε</span>;
                </h1>
                <p class="text-lg text-gray-600">Επιλέξτε τον τρόπο παιχνιδιού και ας ξεκινήσουμε</p>
            </div>

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

            <!-- Game Modes -->
            <div class="grid md:grid-cols-2 gap-6 mb-8">
                <!-- Create Game -->
                <div class="bg-white rounded-2xl border-2 border-gray-100 p-8 hover:border-blue-300 transition-all duration-200 shadow-lg hover:shadow-xl">
                    <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-purple-500 rounded-2xl flex items-center justify-center text-white text-2xl mb-4">
                        ⚡
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-3">Δημιουργία Παιχνιδιού</h2>
                    <p class="text-gray-600 mb-6">Ξεκινήστε ένα νέο παιχνίδι και προκαλέστε έναν φίλο ή το AI</p>

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
                            <label class="block text-sm font-semibold text-gray-700 mb-3">Τύπος Παιχνιδιού</label>
                            <div class="space-y-3">
                                <label class="flex items-center gap-3 p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:bg-blue-50 hover:border-blue-300 transition-all duration-200">
                                    <input type="radio" name="game_type" value="ai" checked class="w-5 h-5 text-blue-600">
                                    <div class="flex-1">
                                        <div class="font-bold text-gray-900">Αντίπαλος AI</div>
                                        <div class="text-sm text-gray-600">Γρήγορο παιχνίδι με τον υπολογιστή</div>
                                    </div>
                                </label>

                                <label class="flex items-center gap-3 p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:bg-green-50 hover:border-green-300 transition-all duration-200">
                                    <input type="radio" name="game_type" value="matchmaking" class="w-5 h-5 text-green-600">
                                    <div class="flex-1">
                                        <div class="font-bold text-gray-900">Γρήγορο Παιχνίδι</div>
                                        <div class="text-sm text-gray-600">Βρείτε γρήγορα έναν τυχαίο αντίπαλο</div>
                                    </div>
                                </label>

                                <label class="flex items-center gap-3 p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:bg-purple-50 hover:border-purple-300 transition-all duration-200">
                                    <input type="radio" name="game_type" value="human" class="w-5 h-5 text-purple-600">
                                    <div class="flex-1">
                                        <div class="font-bold text-gray-900">Ιδιωτικό Παιχνίδι</div>
                                        <div class="text-sm text-gray-600">Λάβετε κωδικό για να μοιραστείτε με φίλο</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-bold py-4 rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200">
                            Δημιουργία Παιχνιδιού
                        </button>
                    </form>
                </div>

                <!-- Join Game -->
                <div class="bg-white rounded-2xl border-2 border-gray-100 p-8 hover:border-green-300 transition-all duration-200 shadow-lg hover:shadow-xl">
                    <div class="w-14 h-14 bg-gradient-to-br from-green-500 to-emerald-500 rounded-2xl flex items-center justify-center text-white text-2xl mb-4">
                        🎯
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-3">Συμμετοχή σε Παιχνίδι</h2>
                    <p class="text-gray-600 mb-6">Εισάγετε κωδικό παιχνιδιού για να συμμετάσχετε με φίλο</p>

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
                    </form>
                </div>
            </div>

            <!-- Game Rules -->
            <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-2xl border-2 border-amber-200 p-8">
                <h3 class="text-2xl font-bold text-gray-900 mb-6 text-center">Γρήγοροι Κανόνες</h3>
                <div class="grid md:grid-cols-4 gap-6 text-center">
                    <div>
                        <div class="text-3xl mb-2">📝</div>
                        <div class="font-bold text-gray-900 mb-1">Επιλέξτε Κατηγορία</div>
                        <div class="text-sm text-gray-600">Από Premier League μέχρι Παγκόσμιο Κύπελλο</div>
                    </div>
                    <div>
                        <div class="text-3xl mb-2">🎚️</div>
                        <div class="font-bold text-gray-900 mb-1">Επιλέξτε Επίπεδο</div>
                        <div class="text-sm text-gray-600">Εύκολο, Μεσαίο ή Δύσκολο</div>
                    </div>
                    <div>
                        <div class="text-3xl mb-2">⏱️</div>
                        <div class="font-bold text-gray-900 mb-1">Νικήστε το Χρόνο</div>
                        <div class="text-sm text-gray-600">60 δευτερόλεπτα ανά ερώτηση</div>
                    </div>
                    <div>
                        <div class="text-3xl mb-2">🏆</div>
                        <div class="font-bold text-gray-900 mb-1">Κερδίστε Πόντους</div>
                        <div class="text-sm text-gray-600">Ο υψηλότερος βαθμός κερδίζει</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
