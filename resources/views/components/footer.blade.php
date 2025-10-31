<footer class="bg-gradient-to-r from-gray-900 to-gray-800 text-gray-300 mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-8">
            <!-- About Section -->
            <div>
                <h3 class="text-white font-bold text-lg mb-4">QuizBall</h3>
                <p class="text-sm text-gray-400">
                    Challenge your knowledge in exciting head-to-head trivia battles!
                </p>
            </div>

            <!-- Quick Links -->
            <div>
                <h3 class="text-white font-semibold text-sm uppercase tracking-wider mb-4">Quick Links</h3>
                <ul class="space-y-2">
                    <li>
                        <a href="{{ route('about') }}" class="text-sm hover:text-white transition-colors duration-200">
                            About Us
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('terms') }}" class="text-sm hover:text-white transition-colors duration-200">
                            Terms of Use
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('privacy') }}" class="text-sm hover:text-white transition-colors duration-200">
                            Privacy Policy
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Support -->
            <div>
                <h3 class="text-white font-semibold text-sm uppercase tracking-wider mb-4">Support</h3>
                <ul class="space-y-2">
                    <li>
                        <a href="{{ route('donate') }}" class="text-sm hover:text-white transition-colors duration-200 inline-flex items-center gap-2">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                            </svg>
                            Donate
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Social -->
            <div>
                <h3 class="text-white font-semibold text-sm uppercase tracking-wider mb-4">Follow Us</h3>
                <div class="flex gap-4">
                    <!-- Add your social media links here -->
                    <a href="#" class="text-gray-400 hover:text-white transition-colors duration-200">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition-colors duration-200">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="border-t border-gray-700 pt-6">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-xs text-gray-400">
                    &copy; {{ date('Y') }} QuizBall.
                </p>
                <p class="text-xs text-gray-500">
                    Φτιαγμένο για φίλους του ποδοσφαίρου που νομίζουν ότι τα ξέρουν όλα.
                </p>
            </div>
        </div>
    </div>
</footer>
