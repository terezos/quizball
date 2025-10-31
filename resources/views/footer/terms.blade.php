<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-3xl text-gray-800 leading-tight text-center">
            {{ __('Terms of Use') }}
        </h2>
    </x-slot>

    <div class="py-12 px-4">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-8 sm:p-12">
                    <div class="prose prose-lg max-w-none">
                        <p class="text-sm text-gray-500 mb-6">Last Updated: {{ date('F d, Y') }}</p>

                        <h3 class="text-xl font-bold text-gray-900 mb-4">1. Acceptance of Terms</h3>
                        <p class="text-gray-700 mb-6">
                            By accessing and using QuizBall, you accept and agree to be bound by the terms and provision 
                            of this agreement. If you do not agree to these terms, please do not use this service.
                        </p>

                        <h3 class="text-xl font-bold text-gray-900 mb-4">2. User Conduct</h3>
                        <p class="text-gray-700 mb-4">You agree to:</p>
                        <ul class="list-disc list-inside text-gray-700 mb-6 space-y-2">
                            <li>Provide accurate information when creating an account</li>
                            <li>Not use the service for any illegal or unauthorized purpose</li>
                            <li>Not cheat, exploit bugs, or use automated tools</li>
                            <li>Maintain the confidentiality of your account credentials</li>
                            <li>Respect other players and maintain a friendly environment</li>
                        </ul>

                        <h3 class="text-xl font-bold text-gray-900 mb-4">3. Fair Play</h3>
                        <p class="text-gray-700 mb-6">
                            Players must not switch tabs during active questions, use external assistance, or engage in 
                            any form of cheating. Violations may result in automatic game forfeiture or account suspension.
                        </p>

                        <h3 class="text-xl font-bold text-gray-900 mb-4">4. Content Rights</h3>
                        <p class="text-gray-700 mb-6">
                            All questions, graphics, and content on QuizBall are the property of QuizBall or its content 
                            suppliers and are protected by copyright laws.
                        </p>

                        <h3 class="text-xl font-bold text-gray-900 mb-4">5. Account Termination</h3>
                        <p class="text-gray-700 mb-6">
                            We reserve the right to suspend or terminate accounts that violate these terms of use or 
                            engage in behavior detrimental to the platform or other users.
                        </p>

                        <h3 class="text-xl font-bold text-gray-900 mb-4">6. Disclaimer</h3>
                        <p class="text-gray-700 mb-6">
                            QuizBall is provided "as is" without any warranties, expressed or implied. We do not guarantee 
                            uninterrupted or error-free service.
                        </p>

                        <h3 class="text-xl font-bold text-gray-900 mb-4">7. Changes to Terms</h3>
                        <p class="text-gray-700 mb-6">
                            We reserve the right to modify these terms at any time. Continued use of the service after 
                            changes constitutes acceptance of the new terms.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
