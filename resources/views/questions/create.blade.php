<x-app-layout>
    <x-slot name="title">QuizBall - Δημιουργία Ερώτησης</x-slot>
    <x-slot name="header">
        <x-page-header title="Δημιουργία Ερώτησης" icon="➕" />
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
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
                <div class="p-4 sm:p-6">
                    <form method="POST" action="{{ route('questions.store') }}" enctype="multipart/form-data" x-data="questionForm()">
                        @csrf

                        <!-- Category -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Κατηγορία *</label>
                            <select name="category_id" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Επίλεξε Κατηγορία</option>
                                @foreach($categoriesBySport as $sport => $categories)
                                    <optgroup label="{{ $sport === 'football' ? '⚽ Ποδόσφαιρο' : '🏀 Μπάσκετ' }}">
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->icon }} {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Question Text -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Ερώτηση *</label>
                            <textarea name="question_text" rows="3" required
                                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                      placeholder="Προσθέστε την ερώτηση...">{{ old('question_text') }}</textarea>
                            @error('question_text')
                                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Question Type -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Τύπος *</label>
                            <select name="question_type" x-model="questionType" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Επιλέξτε Τύπο Ερώτησης</option>
                                @foreach(\App\Enums\QuestionType::cases() as $type)
                                    <option value="{{ $type->value }}" {{ old('question_type') == $type->value ? 'selected' : '' }}>
                                        {{ $type->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('question_type')
                                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Difficulty -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Επίπεδο *</label>
                            <select name="difficulty" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Επίλεξε Επίπεδο</option>
                                @foreach(\App\Enums\DifficultyLevel::cases() as $difficulties)
                                    <option value="{{ $difficulties->value }}" {{ old('difficulty') == $difficulties->value ? 'selected' : '' }}>
                                        {{ $difficulties->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('difficulty')
                                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Image Upload (only for text_input_with_image) -->
                        <div class="mb-6" x-show="questionType === 'text_input_with_image'" x-cloak>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Εικόνα *</label>
                            <input type="file" name="image" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
                                   :required="questionType === 'text_input_with_image'"
                                   @change="previewImage($event)"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <p class="text-sm text-gray-500 mt-1">Ανέβασε μια εικόνα (JPG, PNG, GIF, WebP - max 5MB). πχ: βιογραφικό παίκτη, παίκτης που λείπε</p>

                            <!-- Image Preview -->
                            <div x-show="imagePreview" class="mt-3">
                                <img :src="imagePreview" alt="Preview" class="w-full max-w-md rounded border shadow-sm">
                                <button type="button" @click="clearImagePreview()" class="mt-2 text-sm text-red-600 hover:text-red-800">
                                    ✕ Διαγραφή Εικόνας
                                </button>
                            </div>

                            @error('image')
                                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Source URL -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Πηγή *</label>
                            <input type="url" name="source_url" value="{{ old('source_url') }}" required
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                   placeholder="https://example.com/source-article">
                            <p class="text-sm text-gray-500 mt-1">Πρόσθεσε ένα link για επαλήθευση της απάντησης</p>
                            @error('source_url')
                                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Answers -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Απαντήσεις *</label>

                            <!-- Text Input -->
                            <div x-show="questionType === 'text_input'" x-cloak>
                                <input type="text" name="answers[0][text]" value="{{ old('answers.0.text') }}"
                                       :required="questionType === 'text_input'"
                                       :disabled="questionType !== 'text_input'"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       placeholder="Δώσε τη σωστή απάντηση">
                                <input type="hidden" name="answers[0][is_correct]" value="1" :disabled="questionType !== 'text_input'">
                                @error('answers.0.text')
                                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                                @enderror
                                @error('answers')
                                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Text Input with Image -->
                            <div x-show="questionType === 'text_input_with_image'" x-cloak>
                                <input type="text" name="answers[0][text]" value="{{ old('answers.0.text') }}"
                                       :required="questionType === 'text_input_with_image'"
                                       :disabled="questionType !== 'text_input_with_image'"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       placeholder="Δώσε τη σωστή απάντηση">
                                <input type="hidden" name="answers[0][is_correct]" value="1" :disabled="questionType !== 'text_input_with_image'">
                                @error('answers.0.text')
                                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                                @enderror
                                @error('answers')
                                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Multiple Choice -->
                            <div x-show="questionType === 'multiple_choice'" x-cloak class="space-y-3">
                                @for($i = 0; $i < 4; $i++)
                                    <div class="flex gap-3">
                                        <input type="radio" name="correct_answer_id" value="{{ $i }}"
                                               {{ old('correct_answer_id') == $i ? 'checked' : '' }}
                                               :required="questionType === 'multiple_choice'"
                                               :disabled="questionType !== 'multiple_choice'"
                                               class="mt-3">
                                        <input type="text" name="answers[{{ $i }}][text]" value="{{ old('answers.'.$i.'.text') }}"
                                               class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                               placeholder="Απάντηση {{ $i + 1 }}"
                                               :required="questionType === 'multiple_choice'"
                                               :disabled="questionType !== 'multiple_choice'">
                                    </div>
                                @endfor
                                @error('correct_answer_id')
                                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                                @enderror
                                @error('answers')
                                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                                @enderror
                                <p class="text-xs text-gray-500 mt-1">Επίλεξε το κουμπί επιλογής δίπλα στη σωστή απάντηση.</p>
                            </div>

                            <!-- Top 5 -->
                            <div x-show="questionType === 'top_5'" x-cloak class="space-y-2">
                                <template x-for="(answer, index) in top5Answers" :key="answer.id">
                                    <div class="flex gap-2">
                                        <input type="text" :name="'answers[' + index + '][text]'"
                                               x-model="answer.text"
                                               class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                               :placeholder="'Σωστή απάντηση ' + (index + 1)"
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
                                    + Προσθέστε Απάντηση
                                </button>
                                @error('answers')
                                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                                @enderror
                                <p class="text-xs text-gray-500 mt-1">Οι παίκτες πρέπει να δώσουν 5 σωστές απαντήσεις με οποιαδήποτε σειρά.</p>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-3 sm:gap-4">
                            <button type="submit" class="w-full sm:flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition">
                                Δημιουργία Ερώτησης
                            </button>
                            <a href="{{ route('questions.index') }}" class="w-full sm:flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-3 px-6 rounded-lg text-center transition">
                                Ακύρωση
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function questionForm() {
            const oldAnswers = @json(old('answers', []));
            const oldQuestionType = '{{ old('question_type', '') }}';

            return {
                questionType: oldQuestionType,
                top5Answers: [],
                nextId: 0,
                imagePreview: null,

                init() {
                    // Initialize with old data or default 5 answers
                    const count = Object.keys(oldAnswers).length > 0 ? Math.max(5, Object.keys(oldAnswers).length) : 5;
                    for (let i = 0; i < count; i++) {
                        this.top5Answers.push({
                            id: this.nextId++,
                            text: oldAnswers[i] ? oldAnswers[i].text : ''
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
