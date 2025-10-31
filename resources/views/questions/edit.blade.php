<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Question
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <strong class="font-bold">Validation Errors:</strong>
                    <ul class="mt-2 list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('questions.update', $question) }}" enctype="multipart/form-data" x-data="questionForm('{{ $question->question_type->value }}', {{ $question->answers->count() }})">
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
                                <option value="text_input_with_image" {{ $question->question_type->value === 'text_input_with_image' ? 'selected' : '' }}>Text Input with Image</option>
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

                        <!-- Image Upload (only for text_input_with_image) -->
                        <div class="mb-6" x-show="questionType === 'text_input_with_image'" x-cloak>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Image</label>
                            
                            <!-- Current/Preview Image -->
                            @if($question->image_url)
                            <div x-show="!imagePreview" class="mb-3">
                                <img src="{{ asset($question->image_url) }}" 
                                     alt="Question image" 
                                     class="max-w-md rounded border shadow-sm">
                                <p class="text-sm text-gray-500 mt-1">Current image</p>
                            </div>
                            @endif
                            
                            <!-- New Image Preview -->
                            <div x-show="imagePreview" class="mb-3">
                                <img :src="imagePreview" 
                                     alt="Preview" 
                                     class="max-w-md rounded border shadow-sm">
                                <p class="text-sm text-gray-500 mt-1">Preview of new image</p>
                                <button type="button" 
                                        @click="clearImagePreview()" 
                                        class="mt-2 text-sm text-red-600 hover:text-red-800">
                                    ✕ Remove new image
                                </button>
                            </div>
                            
                            <input type="file" name="image" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
                                   @change="previewImage($event)"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <p class="text-sm text-gray-500 mt-1">Upload a new image to replace the current one (JPG, PNG, GIF, WebP - max 5MB)</p>
                            @error('image')
                                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Source URL -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Source URL *</label>
                            <input type="url" name="source_url" value="{{ old('source_url', $question->source_url) }}" required
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                   placeholder="https://example.com/source-article">
                            <p class="text-sm text-gray-500 mt-1">Add a link to verify the correct answer (will be shown after game ends)</p>
                            @error('source_url')
                                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Answers -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Answers *</label>

                            <!-- Text Input -->
                            <div x-show="questionType === 'text_input'" x-cloak>
                                <input type="text" name="answers[0][text]" value="{{ $question->answers->first()?->answer_text }}"
                                       :required="questionType === 'text_input'"
                                       :disabled="questionType !== 'text_input'"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <input type="hidden" name="answers[0][is_correct]" value="1" :disabled="questionType !== 'text_input'">
                            </div>

                            <!-- Text Input with Image -->
                            <div x-show="questionType === 'text_input_with_image'" x-cloak>
                                <input type="text" name="answers[0][text]" value="{{ $question->answers->first()?->answer_text }}"
                                       :required="questionType === 'text_input_with_image'"
                                       :disabled="questionType !== 'text_input_with_image'"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       placeholder="Enter the correct answer">
                                <input type="hidden" name="answers[0][is_correct]" value="1" :disabled="questionType !== 'text_input_with_image'">
                                <p class="text-xs text-gray-500 mt-1">Answers are case-insensitive and whitespace is normalized</p>
                            </div>

                            <!-- Multiple Choice -->
                            <div x-show="questionType === 'multiple_choice'" x-cloak class="space-y-3">
                                @php
                                    $answers = $question->answers->sortBy('order')->values();
                                    $correctIndex = $answers->search(fn($a) => $a->is_correct);
                                @endphp
                                @for($i = 0; $i < 4; $i++)
                                    <div class="flex gap-3">
                                        <input type="radio" name="correct_answer_id" value="{{ $i }}"
                                               {{ $correctIndex === $i ? 'checked' : '' }}
                                               :required="questionType === 'multiple_choice'"
                                               :disabled="questionType !== 'multiple_choice'"
                                               class="mt-3">
                                        <input type="text" name="answers[{{ $i }}][text]" value="{{ $answers[$i]->answer_text ?? '' }}"
                                               class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                               :required="questionType === 'multiple_choice'"
                                               :disabled="questionType !== 'multiple_choice'">
                                    </div>
                                @endfor
                            </div>

                            <!-- Top 5 -->
                            <div x-show="questionType === 'top_5'" x-cloak class="space-y-2">
                                <template x-for="(answer, index) in top5Answers" :key="answer.id">
                                    <div class="flex gap-2">
                                        <input type="text" :name="'answers[' + index + '][text]'"
                                               x-model="answer.text"
                                               class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                               :placeholder="'Correct answer ' + (index + 1)"
                                               :required="questionType === 'top_5' && index < 5"
                                               :disabled="questionType !== 'top_5'">
                                        <input type="hidden" :name="'answers[' + index + '][is_correct]'" value="1" :disabled="questionType !== 'top_5'">
                                        <button type="button" 
                                                @click="removeAnswer(answer.id)"
                                                x-show="top5Answers.length > 5"
                                                class="px-3 py-2 bg-red-500 hover:bg-red-600 text-white rounded-md transition">
                                            ✕
                                        </button>
                                    </div>
                                </template>
                                <button type="button" @click="addAnswer()" x-show="questionType === 'top_5'" class="mt-2 text-sm text-blue-600 hover:text-blue-800">
                                    + Add More Answers
                                </button>
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
            const existingAnswers = @json($question->answers->sortBy('order')->values()->pluck('answer_text'));
            
            return {
                questionType: initialType,
                top5Answers: [],
                nextId: 0,
                imagePreview: null,

                init() {
                    // Initialize with existing answers or default 5
                    const count = Math.max(5, existingAnswers.length);
                    for (let i = 0; i < count; i++) {
                        this.top5Answers.push({
                            id: this.nextId++,
                            text: existingAnswers[i] || ''
                        });
                    }
                },

                addAnswer() {
                    this.top5Answers.push({
                        id: this.nextId++,
                        text: ''
                    });
                },

                removeAnswer(id) {
                    if (this.top5Answers.length > 5) {
                        this.top5Answers = this.top5Answers.filter(a => a.id !== id);
                    }
                },

                previewImage(event) {
                    const file = event.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.imagePreview = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    }
                },

                clearImagePreview() {
                    this.imagePreview = null;
                    const input = document.querySelector('input[name="image"]');
                    if (input) input.value = '';
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
