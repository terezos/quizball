<section class="space-y-6">
    <header>
        <h2 class="text-2xl font-bold text-red-600">
            Διαγραφή Λογαριασμού
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            Μόλις διαγραφεί ο λογαριασμός σας, όλα τα δεδομένα του θα διαγραφούν οριστικά. Πριν διαγράψετε τον λογαριασμό σας, κατεβάστε τυχόν δεδομένα που θέλετε να διατηρήσετε.
        </p>
    </header>

    <button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="inline-flex items-center gap-2 px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200"
    >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
        </svg>
        Διαγραφή Λογαριασμού
    </button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-gray-900">
                Είστε σίγουροι ότι θέλετε να διαγράψετε τον λογαριασμό σας;
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Μόλις διαγραφεί ο λογαριασμός σας, όλα τα δεδομένα του θα διαγραφούν οριστικά. Παρακαλώ εισάγετε τον κωδικό σας για να επιβεβαιώσετε ότι θέλετε να διαγράψετε οριστικά τον λογαριασμό σας.
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="Κωδικός" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-3/4"
                    placeholder="Κωδικός"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" x-on:click="$dispatch('close')" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition-colors duration-200">
                    Ακύρωση
                </button>

                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition-colors duration-200">
                    Διαγραφή Λογαριασμού
                </button>
            </div>
        </form>
    </x-modal>
</section>
