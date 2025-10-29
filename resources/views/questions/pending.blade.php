<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Pending Questions Approval
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($questions->isEmpty())
                        <div class="text-center py-8 text-gray-500">
                            <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-lg font-medium">No pending questions!</p>
                            <p class="text-sm">All questions have been reviewed.</p>
                        </div>
                    @else
                        <div class="space-y-6">
                            @foreach($questions as $question)
                                <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                                    <!-- Question Info -->
                                    <div class="flex items-start justify-between mb-4">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-2">
                                                <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded">
                                                    {{ $question->category->icon }} {{ $question->category->name }}
                                                </span>
                                                <span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs font-semibold rounded">
                                                    {{ ucfirst($question->difficulty->value) }}
                                                </span>
                                                <span class="px-2 py-1 bg-purple-100 text-purple-800 text-xs font-semibold rounded">
                                                    {{ str_replace('_', ' ', ucfirst($question->question_type->value)) }}
                                                </span>
                                            </div>
                                            <h3 class="text-lg font-bold text-gray-900 mb-2">{{ $question->question_text }}</h3>
                                            <p class="text-sm text-gray-600">
                                                Created by: <span class="font-medium">{{ $question->creator->name }}</span>
                                                <span class="mx-2">•</span>
                                                <span class="text-gray-500">{{ $question->created_at->diffForHumans() }}</span>
                                                @if($question->creator->is_pre_validated)
                                                    <span class="ml-2 px-2 py-0.5 bg-green-100 text-green-800 text-xs rounded">Pre-validated</span>
                                                @endif
                                                @if($question->creator->approved_questions_count > 0)
                                                    <span class="ml-2 text-xs text-gray-500">({{ $question->creator->approved_questions_count }} approved)</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Answers -->
                                    <div class="mb-4">
                                        <h4 class="text-sm font-semibold text-gray-700 mb-2">Answers:</h4>
                                        <div class="space-y-1">
                                            @foreach($question->answers as $answer)
                                                <div class="flex items-center gap-2">
                                                    @if($answer->is_correct)
                                                        <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                        </svg>
                                                    @else
                                                        <svg class="w-5 h-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                                        </svg>
                                                    @endif
                                                    <span class="text-sm {{ $answer->is_correct ? 'text-green-700 font-semibold' : 'text-gray-600' }}">
                                                        {{ $answer->answer_text }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Source URL -->
                                    @if($question->source_url)
                                        <div class="mb-4">
                                            <h4 class="text-sm font-semibold text-gray-700 mb-1">Source:</h4>
                                            <a href="{{ $question->source_url }}" target="_blank" class="text-sm text-blue-600 hover:underline break-all">
                                                {{ $question->source_url }}
                                            </a>
                                        </div>
                                    @endif

                                    <!-- Actions -->
                                    <div class="flex gap-3 pt-4 border-t border-gray-200" x-data="{ showReject: false }">
                                        <!-- Approve -->
                                        <form method="POST" action="{{ route('questions.approve', $question) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-colors">
                                                ✓ Approve
                                            </button>
                                        </form>

                                        <!-- Reject -->
                                        <button @click="showReject = !showReject" type="button" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition-colors">
                                            ✗ Reject
                                        </button>

                                        <!-- Edit -->
                                        <a href="{{ route('questions.edit', $question) }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition-colors">
                                            Edit
                                        </a>

                                        <!-- Reject Form (Hidden by default) -->
                                        <div x-show="showReject" x-cloak class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.away="showReject = false">
                                            <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4" @click.stop>
                                                <h3 class="text-lg font-bold mb-4">Reject Question</h3>
                                                <form method="POST" action="{{ route('questions.reject', $question) }}">
                                                    @csrf
                                                    <div class="mb-4">
                                                        <label class="block text-sm font-medium text-gray-700 mb-2">Reason for rejection *</label>
                                                        <textarea name="rejection_reason" rows="4" required
                                                                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                                                                  placeholder="Explain why this question is being rejected..."></textarea>
                                                    </div>
                                                    <div class="flex gap-2 justify-end">
                                                        <button type="button" @click="showReject = false" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg">
                                                            Cancel
                                                        </button>
                                                        <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg">
                                                            Confirm Reject
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6">
                            {{ $questions->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
