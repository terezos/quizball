<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-3xl text-gray-800 leading-tight text-center">
            {{ __('About Us') }}
        </h2>
    </x-slot>

    <div class="py-12 px-4">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-8 sm:p-12">
                    <div class="prose prose-lg max-w-none">
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">Welcome to QuizBall!</h3>
                        
                        <p class="text-gray-700 mb-6">
                            QuizBall is an exciting head-to-head trivia game platform where knowledge meets competition. 
                            Challenge your friends or match up with players from around the world in thrilling quiz battles.
                        </p>

                        <h4 class="text-xl font-semibold text-gray-900 mb-3">Our Mission</h4>
                        <p class="text-gray-700 mb-6">
                            We believe learning should be fun and engaging. Our mission is to create an entertaining 
                            platform where players can test their knowledge, learn new facts, and compete in a 
                            friendly environment.
                        </p>

                        <h4 class="text-xl font-semibold text-gray-900 mb-3">How It Works</h4>
                        <ul class="list-disc list-inside text-gray-700 mb-6 space-y-2">
                            <li>Choose your opponent or get matched automatically</li>
                            <li>Select categories and difficulty levels</li>
                            <li>Answer questions within the time limit</li>
                            <li>Earn points based on difficulty and accuracy</li>
                            <li>Track your progress and compete for the top spot</li>
                        </ul>

                        <h4 class="text-xl font-semibold text-gray-900 mb-3">Contact Us</h4>
                        <p class="text-gray-700">
                            Have questions or feedback? We'd love to hear from you!<br>
                            Email us at: <a href="mailto:info@quizball.com" class="text-blue-600 hover:text-blue-800">info@quizball.com</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
