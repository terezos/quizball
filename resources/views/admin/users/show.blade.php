<x-app-layout>
    <x-slot name="header">
        <x-page-header title="User Details" icon="👤">
            <x-slot:actions>
                <x-header-button href="{{ route('admin.users.index') }}" variant="secondary" icon="←">
                    Back to Users
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
                                <div class="flex gap-2">
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full
                                        {{ $user->role->value === 'admin' ? 'bg-purple-100 text-purple-800' : '' }}
                                        {{ $user->role->value === 'editor' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $user->role->value === 'user' ? 'bg-gray-100 text-gray-800' : '' }}">
                                        {{ ucfirst($user->role->value) }}
                                    </span>
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full
                                        {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                    @if($user->is_pre_validated)
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            Pre-validated ✓
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="text-sm text-gray-500">
                                <p>Member since {{ $user->created_at->format('F j, Y') }}</p>
                            </div>
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
                            <div class="text-sm text-gray-600">Questions Created</div>
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
                            <div class="text-sm text-gray-600">Approved Questions</div>
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
                            <div class="text-sm text-gray-600">Games Played</div>
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
                            <div class="text-sm text-gray-600">Total Score</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            @if($user->id !== auth()->id())
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">User Actions</h3>
                <div class="flex flex-col sm:flex-row gap-4">
                    <!-- Change Role -->
                    <form method="POST" action="{{ route('admin.users.updateRole', $user) }}" class="flex-1">
                        @csrf
                        @method('PATCH')
                        <div class="flex gap-2">
                            <select name="role" class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="user" {{ $user->role->value === 'user' ? 'selected' : '' }}>Player</option>
                                <option value="editor" {{ $user->role->value === 'editor' ? 'selected' : '' }}>Editor</option>
                                <option value="admin" {{ $user->role->value === 'admin' ? 'selected' : '' }}>Admin</option>
                            </select>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-lg transition">
                                Update Role
                            </button>
                        </div>
                    </form>

                    <!-- Toggle Status -->
                    <form method="POST" action="{{ route('admin.users.toggleStatus', $user) }}" class="flex-shrink-0">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="w-full bg-{{ $user->is_active ? 'orange' : 'green' }}-600 hover:bg-{{ $user->is_active ? 'orange' : 'green' }}-700 text-white font-semibold px-6 py-2 rounded-lg transition">
                            {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>

                    <!-- Delete User -->
                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                          onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');"
                          class="flex-shrink-0">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold px-6 py-2 rounded-lg transition">
                            Delete User
                        </button>
                    </form>
                </div>
            </div>
            @endif

            <!-- Recent Questions -->
            @if($user->questions->count() > 0)
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Questions</h3>
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
                        <a href="{{ route('questions.index') }}?search={{ $user->email }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            View all questions →
                        </a>
                    </div>
                @endif
            </div>
            @endif

            <!-- Game Statistics -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Game Statistics</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <div class="text-2xl font-bold text-gray-900">{{ $gamesPlayed }}</div>
                        <div class="text-sm text-gray-600">Games Played</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-green-600">{{ $gamesWon }}</div>
                        <div class="text-sm text-gray-600">Games Won</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-red-600">{{ $gamesLost }}</div>
                        <div class="text-sm text-gray-600">Games Lost</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-blue-600">{{ $winRate }}%</div>
                        <div class="text-sm text-gray-600">Win Rate</div>
                    </div>
                </div>
            </div>

            <!-- Recent Games -->
            @if($user->gamePlayers->count() > 0)
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Games</h3>
                <div class="space-y-3">
                    @foreach($user->gamePlayers->take(10) as $gamePlayer)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">
                                        Game #{{ $gamePlayer->game->id }} - {{ ucfirst($gamePlayer->game->game_type) }}
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ $gamePlayer->game->created_at->diffForHumans() }}
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-lg font-bold {{ $gamePlayer->score > 0 ? 'text-blue-600' : 'text-gray-400' }}">
                                        {{ $gamePlayer->score }} pts
                                    </div>
                                    @if($gamePlayer->game->status === 'completed')
                                        <span class="text-xs {{ $gamePlayer->is_winner ? 'text-green-600 font-semibold' : 'text-gray-500' }}">
                                            {{ $gamePlayer->is_winner ? '🏆 Winner' : 'Lost' }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-500">{{ ucfirst($gamePlayer->game->status->value) }}</span>
                                    @endif
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
