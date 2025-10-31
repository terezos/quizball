<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-3xl text-gray-800 leading-tight text-center">
            {{ __('Support QuizBall') }}
        </h2>
    </x-slot>

    <div class="py-12 px-4">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-8 sm:p-12">
                    <div class="text-center mb-8">
                        <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-r from-pink-500 to-red-500 rounded-full mb-4">
                            <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <h3 class="text-3xl font-bold text-gray-900 mb-4">Help Keep QuizBall Running!</h3>
                        <p class="text-lg text-gray-600">
                            Your donations help us maintain and improve the platform for everyone.
                        </p>
                    </div>

                    <div class="prose prose-lg max-w-none mb-8">
                        <h4 class="text-xl font-semibold text-gray-900 mb-3">Why Donate?</h4>
                        <p class="text-gray-700 mb-4">
                            QuizBall is a passion project built by trivia enthusiasts for trivia enthusiasts. 
                            Your support helps us:
                        </p>
                        <ul class="list-disc list-inside text-gray-700 mb-6 space-y-2">
                            <li>Keep the servers running smoothly</li>
                            <li>Add new questions and categories regularly</li>
                            <li>Develop new features and improvements</li>
                            <li>Maintain a fair and fun gaming environment</li>
                            <li>Keep QuizBall ad-free and accessible to all</li>
                        </ul>

                        <h4 class="text-xl font-semibold text-gray-900 mb-3">How to Donate</h4>
                        <p class="text-gray-700 mb-6">
                            We accept donations through the following methods:
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <!-- PayPal -->
                        <div class="border-2 border-blue-200 rounded-xl p-6 hover:border-blue-400 transition-colors">
                            <div class="text-center">
                                <div class="text-blue-600 font-bold text-lg mb-2">PayPal</div>
                                <p class="text-gray-600 text-sm mb-4">Quick and secure donation</p>
                                <a href="#" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition-colors">
                                    Donate with PayPal
                                </a>
                            </div>
                        </div>

                        <!-- Crypto -->
                        <div class="border-2 border-purple-200 rounded-xl p-6 hover:border-purple-400 transition-colors">
                            <div class="text-center">
                                <div class="text-purple-600 font-bold text-lg mb-2">Cryptocurrency</div>
                                <p class="text-gray-600 text-sm mb-4">Bitcoin, Ethereum, and more</p>
                                <button class="inline-block bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-6 rounded-lg transition-colors">
                                    View Wallet Addresses
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-200 rounded-xl p-6 text-center">
                        <p class="text-gray-800 font-semibold mb-2">
                            Every contribution, no matter how small, makes a difference!
                        </p>
                        <p class="text-gray-600 text-sm">
                            Thank you for being part of the QuizBall community! ❤️
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
