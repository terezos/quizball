<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Question Management" icon="📝">
            <x-slot:actions>
                @if(auth()->user()->isAdmin())
                    <x-header-button href="{{ route('questions.pending') }}" variant="warning" icon="📋">
                        Pending Approval
                    </x-header-button>
                @endif
                <x-header-button href="{{ route('questions.create') }}" icon="+">
                    Create Question
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
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">🔍 Filter Questions</h3>
                    <form method="GET" action="{{ route('questions.index') }}" id="filterForm">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-3">
                                    Select Categories
                                </label>
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
                            @if(request('categories'))
                                <div class="flex justify-between items-center pt-2">
                                    <span class="text-sm text-gray-600">
                                        {{ count(request('categories')) }} {{ Str::plural('category', count(request('categories'))) }} selected
                                    </span>
                                    <a href="{{ route('questions.index') }}"
                                        class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition">
                                        ✕ Clear Filters
                                    </a>
                                </div>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Question</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Difficulty</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Creator</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($questions as $question)
                                    <tr>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900">{{ Str::limit($question->question_text, 60) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm text-gray-900">{{ $question->category->icon }} {{ $question->category->name }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                                {{ str_replace('_', ' ', $question->question_type->value) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full
                                                {{ $question->difficulty->value === 'easy' ? 'bg-green-100 text-green-800' : '' }}
                                                {{ $question->difficulty->value === 'medium' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                {{ $question->difficulty->value === 'hard' ? 'bg-red-100 text-red-800' : '' }}">
                                                {{ ucfirst($question->difficulty->value) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full
                                                {{ $question->status === 'approved' ? 'bg-green-100 text-green-800' : '' }}
                                                {{ $question->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                {{ $question->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}">
                                                {{ ucfirst($question->status) }}
                                            </span>
                                        </td>
{{--                                        @if(auth()->user()->isAdmin())--}}
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $question->creator->name }}
                                                @if($question->creator->is_pre_validated)
                                                    <span class="ml-1 text-xs text-green-600">✓</span>
                                                @endif
                                            </td>
{{--                                        @endif--}}
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('questions.edit', $question) }}" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                                            <form action="{{ route('questions.destroy', $question) }}" method="POST" class="inline"
                                                  onsubmit="return confirm('Are you sure you want to delete this question?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                            No questions found. <a href="{{ route('questions.create') }}" class="text-blue-600">Create your first question</a>
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
</x-app-layout>
