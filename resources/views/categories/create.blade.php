<x-app-layout>
    <x-slot name="title">QuizBall - Δημιουργία Κατηγορίας</x-slot>
    <x-slot name="header">
        <x-page-header title="Δημιουργία Κατηγορίας" icon="➕" />
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('categories.store') }}">
                        @csrf

                        <!-- Όνομα Κατηγορίας -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Όνομα Κατηγορίας *</label>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                   placeholder="π.χ. Premier League, Champions League">
                            @error('name')
                            <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Εικονίδιο -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Εικονίδιο (Emoji)</label>
                            <input type="text" name="icon" value="{{ old('icon') }}" maxlength="10"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                   placeholder="⚽">
                            <p class="text-sm text-gray-500 mt-1">Προαιρετικό emoji εικονίδιο για εμφάνιση με την κατηγορία</p>
                            @error('icon')
                            <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Άθλημα -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Άθλημα *</label>
                            <div class="grid grid-cols-2 gap-4">
                                <label class="flex items-center justify-center gap-2 p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:bg-blue-50 hover:border-blue-300 transition-all duration-200 has-[:checked]:bg-blue-50 has-[:checked]:border-blue-500">
                                    <input type="radio" name="sport" value="football" {{ old('sport', 'football') === 'football' ? 'checked' : '' }} required class="w-5 h-5 text-blue-600">
                                    <span class="font-semibold text-gray-900">⚽ Ποδόσφαιρο</span>
                                </label>
                                <label class="flex items-center justify-center gap-2 p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:bg-orange-50 hover:border-orange-300 transition-all duration-200 has-[:checked]:bg-orange-50 has-[:checked]:border-orange-500">
                                    <input type="radio" name="sport" value="basketball" {{ old('sport') === 'basketball' ? 'checked' : '' }} required class="w-5 h-5 text-orange-600">
                                    <span class="font-semibold text-gray-900">🏀 Μπάσκετ</span>
                                </label>
                            </div>
                            <p class="text-sm text-gray-500 mt-1">Η σειρά εμφάνισης θα οριστεί αυτόματα</p>
                            @error('sport')
                            <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Ενεργή -->
                        <div class="mb-6">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
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
                                Δημιουργία Κατηγορίας
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
