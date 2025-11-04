<footer class="bg-gradient-to-r from-gray-900 to-gray-800 text-gray-300 mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-8">
                <div class="text-center">
                    <a href="{{ route('about') }}" class="text-sm hover:text-white transition-colors duration-200">
                        Ποιοι Είμαστε
                    </a>
                </div>
                <div class="text-center">
                    <a href="{{ route('terms') }}" class="text-sm hover:text-white transition-colors duration-200">
                        Όροι Χρήσης
                    </a>
                </div>

                <div class="text-center">
                    <a href="{{ route('privacy') }}" class="text-sm hover:text-white transition-colors duration-200">
                       Πολιτική Απορρήτου
                    </a>
                </div>
                <div class="text-center">
                    <a href="{{ route('privacy') }}" class="text-sm hover:text-white transition-colors duration-200">
                        Διαχείριση Cookies
                    </a>
                </div>
            </div>

        <!-- Bottom Bar -->
        <div class="border-t border-gray-700 pt-6">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-xs text-gray-400">
                    &copy; {{ date('Y') }} quizball.io
                </p>
                <p class="text-xs text-gray-500">
                    Φτιαγμένο για φιλάθλους που νομίζουν ότι τα ξέρουν όλα.
                </p>
            </div>
        </div>
    </div>
</footer>
