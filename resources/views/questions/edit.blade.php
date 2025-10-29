<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Question
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('questions.update', $question) }}" x-data="questionForm('{{ $question->question_type->value }}', {{ $question->answers->count() }})">
                        @csrf
                        @method('PUT')

                        <!-- Category -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
                            <select name="category_id" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ $question->category_id == $category->id ? 'selected' : '' }}>
                                        {{ $category->icon }} {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Question Text -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Question Text *</label>
                            <textarea name="question_text" rows="3" required
                                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ $question->question_text }}</textarea>
                        </div>

                        <!-- Question Type -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Question Type *</label>
                            <select name="question_type" x-model="questionType" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="text_input" {{ $question->question_type->value === 'text_input' ? 'selected' : '' }}>Text Input</option>
                                <option value="multiple_choice" {{ $question->question_type->value === 'multiple_choice' ? 'selected' : '' }}>Multiple Choice</option>
                                <option value="top_5" {{ $question->question_type->value === 'top_5' ? 'selected' : '' }}>Top 5</option>
                            </select>
                        </div>

                        <!-- Difficulty -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Difficulty *</label>
                            <select name="difficulty" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="easy" {{ $question->difficulty->value === 'easy' ? 'selected' : '' }}>Easy (1 point)</option>
                                <option value="medium" {{ $question->difficulty->value === 'medium' ? 'selected' : '' }}>Medium (2 points)</option>
                                <option value="hard" {{ $question->difficulty->value === 'hard' ? 'selected' : '' }}>Hard (3 points)</option>
                            </select>
                        </div>

                        <!-- Answers -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Answers *</label>

                            <!-- Text Input -->
                            <div x-show="questionType === 'text_input'" x-cloak>
                                <input type="text" name="answers[0][text]" value="{{ $question->answers->first()?->answer_text }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <input type="hidden" name="answers[0][is_correct]" value="1">
                            </div>

                            <!-- Multiple Choice -->
                            <div x-show="questionType === 'multiple_choice'" x-cloak class="space-y-3">
                                @foreach($question->answers as $index => $answer)
                                    <div class="flex gap-3">
                                        <input type="radio" name="correct_answer_id" value="{{ $index }}"
                                               {{ $answer->is_correct ? 'checked' : '' }} required class="mt-3">
                                        <input type="text" name="answers[{{ $index }}][text]" value="{{ $answer->answer_text }}"
                                               class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Top 5 -->
                            <div x-show="questionType === 'top_5'" x-cloak class="space-y-2">
                                @foreach($question->answers as $index => $answer)
                                    <input type="text" name="answers[{{ $index }}][text]" value="{{ $answer->answer_text }}"
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <input type="hidden" name="answers[{{ $index }}][is_correct]" value="1">
                                @endforeach
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition">
                                Update Question
                            </button>
                            <a href="{{ route('questions.index') }}" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-3 px-6 rounded-lg text-center transition">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function questionForm(initialType, initialCount) {
            return {
                questionType: initialType,
                answerCount: initialCount
            }
        }
    </script>
    @endpush
</x-app-layout>
