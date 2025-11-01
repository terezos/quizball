<x-app-layout>
    <x-slot name="title">QuizBall - Πληροφορίες Χρήστη</x-slot>
    <x-slot name="header">
        <x-page-header title="Πληροφορίες Χρήστη" icon="👤">
            <x-slot:actions>
                <x-header-button href="{{ route('admin.users.index') }}" variant="secondary" icon="←">
                    Επιστροφή στους Χρήστες
                </x-header-button>
            </x-slot:actions>
        </x-page-header>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            <!-- User Profile Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex flex-col md:flex-row md:items-start gap-6">
                        <div class="flex-1">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
                                <div>
                                    <h3 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h3>
                                    <p class="text-gray-600">{{ $user->email }}</p>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full
                                        {{ $user->role->value === 'admin' ? 'bg-purple-100 text-purple-800' : '' }}
                                        {{ $user->role->value === 'editor' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $user->role->value === 'user' ? 'bg-gray-100 text-gray-800' : '' }}">
                                        {{ $user->role->value === 'admin' ? 'Διαχειριστής' : ($user->role->value === 'editor' ? 'Συντάκτης' : 'Χρήστης') }}
                                    </span>
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full
                                        {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $user->is_active ? 'Ενεργός' : 'Ανενεργός' }}
                                    </span>
                                    @if($user->is_pre_validated)
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            Προεγκεκριμένος ✓
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="text-sm text-gray-500">
                                <p>Μέλος από {{ $user->created_at->translatedFormat('F j, Y') }}</p>
                            </div>


                            <!-- Toggle Status Button -->
                            <form method="POST" action="{{ route('admin.users.toggleStatus', $user) }}" class="mt-4">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105
                                    {{ $user->is_active
                                        ? 'bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white'
                                        : 'bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white' }}">
                                    @if($user->is_active)
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                        </svg>
                                        Απενεργοποίηση Χρήστη
                                    @else
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Ενεργοποίηση Χρήστη
                                    @endif
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <span class="text-2xl">📝</span>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-gray-900">{{ $totalQuestionsCount }}</div>
                            <div class="text-sm break-words hyphens-auto text-gray-600">Δημιουργημένες Ερωτήσεις</div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <span class="text-2xl">✓</span>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-gray-900">{{ $user->approved_questions_count }}</div>
                            <div class="text-sm text-gray-600 break-words hyphens-auto">Εγκεκριμένες Ερωτήσεις</div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                            <span class="text-2xl">🎮</span>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-gray-900">{{ $totalGamesCount }}</div>
                            <div class="text-sm text-gray-600 break-words hyphens-auto">Παιχνίδια</div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <span class="text-2xl">🏆</span>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-gray-900">{{ $totalScore }}</div>
                            <div class="text-sm text-gray-600 break-words hyphens-auto">Συνολικό Σκορ</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            @if($user->id !== auth()->id())
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Αλλαγή Ρόλου</h3>
                <div class="flex flex-col sm:flex-row gap-4">
                    <!-- Change Role -->
                    <form method="POST" action="{{ route('admin.users.updateRole', $user) }}" class="flex-1">
                        @csrf
                        @method('PATCH')
                        <div class="flex gap-2">
                            <select name="role" class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="user" {{ $user->role->value === 'user' ? 'selected' : '' }}>Παίκτης</option>
                                <option value="editor" {{ $user->role->value === 'editor' ? 'selected' : '' }}>Συντάκτης</option>
                                <option value="admin" {{ $user->role->value === 'admin' ? 'selected' : '' }}>Διαχειριστής</option>
                            </select>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-lg transition">
                                Ενημέρωση Ρόλου
                            </button>
                        </div>
                    </form>

                    <!-- Delete User -->
                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                          onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');"
                          class="flex-shrink-0">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold px-6 py-2 rounded-lg transition">
                            Διαγραφή Χρήστη
                        </button>
                    </form>
                </div>
            </div>
            @endif

            @if($user->questions->count() > 0)
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Τελευταίες Δημιουργημένες Ερωτήσεις</h3>
                <div class="space-y-3">
                    @foreach($user->questions as $question)
                        <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1">
                                    <p class="text-sm text-gray-900 font-medium">{{ Str::limit($question->question_text, 100) }}</p>
                                    <div class="flex items-center gap-2 mt-2">
                                        <span class="text-xs text-gray-500">{{ $question->category->icon }} {{ $question->category->name }}</span>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                                            {{ $question->status === 'approved' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $question->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $question->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}">
                                            {{ ucfirst($question->status) }}
                                        </span>
                                    </div>
                                </div>
                                <a href="{{ route('questions.edit', $question) }}" class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                                    View
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
                @if($user->questions->count() >= 10)
                    <div class="mt-4 text-center">
                        <a href="{{ route('questions.index') }}?created_by={{ $user->id }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            Δες όλες τις ερωτήσεις του χρήστη →
                        </a>
                    </div>
                @endif
            </div>
            @endif

            <!-- Game Statistics -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Στατιστικά</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <div class="text-2xl font-bold text-gray-900">{{ $gamesPlayed }}</div>
                        <div class="text-sm text-gray-600">Παιχνίδια</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-green-600">{{ $gamesWon }}</div>
                        <div class="text-sm text-gray-600">Νίκες</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-red-600">{{ $gamesLost }}</div>
                        <div class="text-sm text-gray-600">Ήττες</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-blue-600">{{ $winRate }}%</div>
                        <div class="text-sm text-gray-600">Ποσοστό Νικών</div>
                    </div>
                </div>
            </div>

            <!-- Recent Games -->
            @if($user->gamePlayers->count() > 0)
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Πρόσφατα Παιχνίδια</h3>
                <div class="space-y-3">
                    @foreach($user->gamePlayers->take(10) as $gamePlayer)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">
                                        Παιχνίδι #{{ $gamePlayer->game->id }} - {{ ucfirst($gamePlayer->game->game_type) }}
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ $gamePlayer->game->created_at->diffForHumans() }}
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-lg font-bold {{ $gamePlayer->score > 0 ? 'text-blue-600' : 'text-gray-400' }}">
                                        {{ $gamePlayer->score }} πόντοι
                                    </div>
                                    <span class="text-xs {{ $gamePlayer->is_winner ? 'text-green-600 font-semibold' : 'text-red-500' }}">
                                            {{ $gamePlayer->is_winner ? '🏆 Νίκη' : 'Ήττα' }}
                                        </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
