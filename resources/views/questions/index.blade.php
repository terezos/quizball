<x-app-layout>
    <x-slot name="title">QuizBall - Ερωτήσεις</x-slot>
    <x-slot name="header">
        <x-page-header title="Ερωτήσεις" icon="📝">
            <x-slot:actions>
                @if(auth()->user()->isAdmin())
                    <x-header-button href="{{ route('questions.pending') }}" variant="warning" icon="📋">
                        Προς Έγκριση
                    </x-header-button>
                @endif
                <x-header-button href="{{ route('questions.create') }}" icon="+">
                    Δημιουργία Ερώτησης
                </x-header-button>
            </x-slot:actions>
        </x-page-header>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">🔍 Φίλτρα</h3>
                    <form method="GET" action="{{ route('questions.index') }}" id="filterForm">
                        <div class="space-y-6">
                            <!-- Categories Filter -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-3">
                                    Επίλεξε Κατηγορίες
                                </label>
                                
                                @foreach($categoriesBySport as $sport => $categories)
                                    <div class="mb-6">
                                        <h4 class="text-md font-semibold text-gray-800 mb-3 flex items-center gap-2">
                                            <span class="text-2xl">{{ $sport === 'football' ? '⚽' : '🏀' }}</span>
                                            <span>{{ $sport === 'football' ? 'Ποδόσφαιρο' : 'Μπάσκετ' }}</span>
                                        </h4>
                                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                                            @foreach($categories as $category)
                                                <label class="relative flex items-center p-3 cursor-pointer rounded-lg border-2 transition-all
                                                    {{ in_array($category->id, request('categories', [])) ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300 bg-white' }}">
                                                    <input type="checkbox" name="categories[]" value="{{ $category->id }}"
                                                        {{ in_array($category->id, request('categories', [])) ? 'checked' : '' }}
                                                        class="sr-only peer"
                                                        onchange="document.getElementById('filterForm').submit()">
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-2xl">{{ $category->icon }}</span>
                                                        <span class="text-sm font-medium text-gray-900">{{ $category->name }}</span>
                                                    </div>
                                                    <svg class="absolute top-2 right-2 w-5 h-5 text-blue-600 {{ in_array($category->id, request('categories', [])) ? '' : 'hidden' }}" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                    </svg>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            @if(auth()->user()->isAdmin() && $editors->count() > 0)
                                <!-- Editor Filter -->
                                <div>
                                    <label for="created_by" class="block text-sm font-medium text-gray-700 mb-3">
                                        Φιλτράρισμα ανά Δημιουργό
                                    </label>
                                    <div class="flex gap-2">
                                        <select name="created_by" id="created_by"
                                            class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            onchange="document.getElementById('filterForm').submit()">
                                            <option value="">Όλοι οι Δημιουργοί</option>
                                            @foreach($editors as $userId => $editorName)
                                                <option value="{{ $userId }}" {{ request('created_by') == $userId ? 'selected' : '' }}>
                                                    {{ $editorName }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if(request('created_by'))
                                            <a href="{{ route('questions.index', request()->except('created_by')) }}"
                                                class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition">
                                                ✕
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            @if(request('categories') || request('created_by'))
                                <div class="flex justify-between items-center pt-2 border-t border-gray-200">
                                    <span class="text-sm text-gray-600">
                                        @if(request('categories'))
                                            {{ count(request('categories')) }} κατηγορί{{ count(request('categories')) > 1 ? 'ες' : 'α' }} επιλεγμέν{{ count(request('categories')) > 1 ? 'ες' : 'η' }}.
                                        @endif
                                        @if(request('created_by'))
                                            Δημιουργός: <strong>{{ $editors[request('created_by')] ?? '' }}</strong>
                                        @endif
                                    </span>
                                    <a href="{{ route('questions.index') }}"
                                        class="inline-flex items-center px-4 py-2 bg-red-100 hover:bg-red-200 text-red-700 font-medium rounded-lg transition">
                                        ✕ Καθαρισμός Όλων των Φίλτρων
                                    </a>
                                </div>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-2">
                    <div class="">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ΕΡΩΤΗΣΗ</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ΚΑΤΗΓΟΡΙΑ</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">ΤΥΠΟΣ</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ΔΥΣΚΟΛΙΑ</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">ΚΑΤΑΣΤΑΣΗ</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ΔΗΜΙΟΥΡΓΟΣ</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ΕΝΕΡΓΕΙΕΣ</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($questions as $question)
                                    <tr>
                                        <td class="px-6 py-4 break-words w-100">
                                            <div class="text-sm text-gray-900">{{ Str::limit($question->question_text, 60) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap w-20">
                                            <span class="text-sm text-gray-900">{{ $question->category->icon }} {{ $question->category->name }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                                {{ str_replace('_', ' ', $question->question_type->label()) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full
                                                {{ $question->difficulty->badgeClasses() }}">
                                                {{ $question->difficulty->label() }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-2">
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full
                                                    {{ $question->status->badgeClasses() }}">
                                                    {{ $question->status->label() }}
                                                </span>
                                                @if($question->status === \App\Enums\QuestionStatus::Rejected && $question->rejection_reason)
                                                    <button
                                                        onclick="openRejectionModal({{ $question->id }})"
                                                        class="text-red-600 hover:text-red-800 transition"
                                                        title="Δες λόγο απόρριψης">
                                                        <i class="fas fa-info-circle"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
{{--                                        @if(auth()->user()->isAdmin())--}}
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $question->creator->name }}
                                                @if($question->creator->is_pre_validated)
                                                    <span class="ml-1 text-xs text-green-600">✓</span>
                                                @endif
                                            </td>
{{--                                        @endif--}}
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center">
                                            <a href="{{ route('questions.edit', $question) }}" class="text-blue-600 hover:text-blue-900 mr-3"> <i class="fas fa-edit"></i></a>
                                            <form action="{{ route('questions.destroy', $question) }}" method="POST" class="inline"
                                                  onsubmit="return confirm('Are you sure you want to delete this question?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900"> <i class="fas fa-trash-alt"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                            Δεν έχεις δημιουργήσει ερωτήσεις. <a href="{{ route('questions.create') }}" class="text-blue-600">Δημιούργησε την πρώτη σου</a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $questions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rejection Modal -->
    <div id="rejectionModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-lg bg-white">
            <div class="flex justify-between items-center pb-3 border-b">
                <h3 class="text-xl font-semibold text-red-600">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Λόγος Απόρριψης
                </h3>
                <button onclick="closeRejectionModal()" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
            <div class="mt-4">
                <div id="rejectionContent" class="text-gray-700 text-base leading-relaxed mb-6 p-4 bg-red-50 rounded-lg border border-red-200">
                    <!-- Content will be inserted here -->
                </div>
                <div class="flex justify-end gap-3">
                    <button onclick="closeRejectionModal()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-lg transition">
                        Κλείσιμο
                    </button>
                    <a id="editQuestionLink" href="#" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
                        <i class="fas fa-edit mr-1"></i> Διόρθωση Ερώτησης
                    </a>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        const rejectionData = {
            @foreach($questions as $question)
                @if($question->status === \App\Enums\QuestionStatus::Rejected && $question->rejection_reason)
                    {{ $question->id }}: {
                        reason: {!! json_encode(e($question->rejection_reason)) !!},
                        editUrl: "{{ route('questions.edit', $question) }}"
                    },
                @endif
            @endforeach
        };

        function openRejectionModal(questionId) {
            const data = rejectionData[questionId];
            if (data) {
                document.getElementById('rejectionContent').textContent = data.reason;
                document.getElementById('editQuestionLink').href = data.editUrl;
                document.getElementById('rejectionModal').classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeRejectionModal() {
            document.getElementById('rejectionModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Close modal when clicking outside
        document.getElementById('rejectionModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeRejectionModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeRejectionModal();
            }
        });
    </script>
    @endpush
</x-app-layout>
