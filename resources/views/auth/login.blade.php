<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900 text-center">Σύνδεση</h2>
        <p class="text-sm text-gray-600 text-center mt-2">Καλώς ήρθατε πίσω στο QuizBall</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                   class="w-full rounded-xl border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 px-4 py-3 transition-all duration-200">
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Κωδικός</label>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                   class="w-full rounded-xl border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 px-4 py-3 transition-all duration-200">
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center">
            <input id="remember_me" type="checkbox" name="remember" class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
            <label for="remember_me" class="ml-2 text-sm text-gray-600">Να με θυμάσαι</label>
        </div>

        <div class="space-y-3">
            <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-bold py-3 px-4 rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200">
                Σύνδεση
            </button>

            @if (Route::has('password.request'))
                <div class="text-center">
                    <a href="{{ route('password.request') }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                        Ξεχάσατε τον κωδικό σας;
                    </a>
                </div>
            @endif
        </div>

        <div class="pt-4 border-t border-gray-200 text-center">
            <p class="text-sm text-gray-600">
                Δεν έχετε λογαριασμό;
                <a href="{{ route('register') }}" class="text-blue-600 hover:text-blue-800 font-semibold">
                    Εγγραφή
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>
