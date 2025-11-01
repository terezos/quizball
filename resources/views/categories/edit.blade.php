<x-app-layout>
    <x-slot name="title">QuizBall - Επεξεργασία Κατηγορίας</x-slot>
    <x-slot name="header">
        <x-page-header title="Επεξεργασία Κατηγορίας" icon="✏️" />
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('categories.update', $category) }}">
                        @csrf
                        @method('PUT')

                        <!-- Όνομα Κατηγορίας -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Όνομα Κατηγορίας *</label>
                            <input type="text" name="name" value="{{ old('name', $category->name) }}" required
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                   placeholder="π.χ. Premier League, Champions League">
                            @error('name')
                            <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Εικονίδιο -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Εικονίδιο (Emoji)</label>
                            <input type="text" name="icon" value="{{ old('icon', $category->icon) }}" maxlength="10"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                   placeholder="⚽">
                            <p class="text-sm text-gray-500 mt-1">Προαιρετικό emoji εικονίδιο για εμφάνιση με την κατηγορία</p>
                            @error('icon')
                            <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Σειρά Εμφάνισης -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Σειρά Εμφάνισης *</label>
                            <input type="number" name="order" value="{{ old('order', $category->order) }}" required min="0"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                   placeholder="0">
                            <p class="text-sm text-gray-500 mt-1">Οι μικρότεροι αριθμοί εμφανίζονται πρώτοι</p>
                            @error('order')
                            <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Ενεργή -->
                        <div class="mb-6">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">Ενεργή (ορατή στους χρήστες)</span>
                            </label>
                            @error('is_active')
                            <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Κουμπιά -->
                        <div class="flex items-center justify-end gap-4 mt-8">
                            <a href="{{ route('categories.index') }}" class="text-gray-600 hover:text-gray-900">Ακύρωση</a>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition">
                                Ενημέρωση Κατηγορίας
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
