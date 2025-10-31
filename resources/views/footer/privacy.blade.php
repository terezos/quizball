<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-3xl text-gray-800 leading-tight text-center">
            {{ __('Privacy Policy') }}
        </h2>
    </x-slot>

    <div class="py-12 px-4">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-8 sm:p-12">
                    <div class="prose prose-lg max-w-none">
                        <p class="text-sm text-gray-500 mb-6">Last Updated: {{ date('F d, Y') }}</p>

                        <h3 class="text-xl font-bold text-gray-900 mb-4">1. Information We Collect</h3>
                        <p class="text-gray-700 mb-4">We collect the following types of information:</p>
                        <ul class="list-disc list-inside text-gray-700 mb-6 space-y-2">
                            <li><strong>Account Information:</strong> Username, email address, and password</li>
                            <li><strong>Game Data:</strong> Your game history, scores, and statistics</li>
                            <li><strong>Usage Data:</strong> How you interact with our platform</li>
                            <li><strong>Device Information:</strong> Browser type, IP address, and device identifiers</li>
                        </ul>

                        <h3 class="text-xl font-bold text-gray-900 mb-4">2. How We Use Your Information</h3>
                        <p class="text-gray-700 mb-4">We use your information to:</p>
                        <ul class="list-disc list-inside text-gray-700 mb-6 space-y-2">
                            <li>Provide and maintain our service</li>
                            <li>Match you with other players</li>
                            <li>Track your progress and display leaderboards</li>
                            <li>Improve our platform and user experience</li>
                            <li>Prevent fraud and ensure fair play</li>
                            <li>Send you important updates about the service</li>
                        </ul>

                        <h3 class="text-xl font-bold text-gray-900 mb-4">3. Information Sharing</h3>
                        <p class="text-gray-700 mb-6">
                            We do not sell, trade, or rent your personal information to third parties. Your display name 
                            and game statistics may be visible to other players. We may share aggregated, anonymized data 
                            for analytical purposes.
                        </p>

                        <h3 class="text-xl font-bold text-gray-900 mb-4">4. Cookies and Tracking</h3>
                        <p class="text-gray-700 mb-6">
                            We use cookies and similar tracking technologies to track activity on our service and hold 
                            certain information. You can instruct your browser to refuse all cookies or to indicate when 
                            a cookie is being sent.
                        </p>

                        <h3 class="text-xl font-bold text-gray-900 mb-4">5. Data Security</h3>
                        <p class="text-gray-700 mb-6">
                            We implement appropriate security measures to protect your personal information. However, 
                            no method of transmission over the Internet is 100% secure, and we cannot guarantee absolute security.
                        </p>

                        <h3 class="text-xl font-bold text-gray-900 mb-4">6. Your Rights</h3>
                        <p class="text-gray-700 mb-4">You have the right to:</p>
                        <ul class="list-disc list-inside text-gray-700 mb-6 space-y-2">
                            <li>Access and update your personal information</li>
                            <li>Request deletion of your account and data</li>
                            <li>Opt-out of promotional communications</li>
                            <li>Object to certain data processing activities</li>
                        </ul>

                        <h3 class="text-xl font-bold text-gray-900 mb-4">7. Children's Privacy</h3>
                        <p class="text-gray-700 mb-6">
                            Our service is not intended for children under 13 years of age. We do not knowingly collect 
                            personal information from children under 13.
                        </p>

                        <h3 class="text-xl font-bold text-gray-900 mb-4">8. Changes to Privacy Policy</h3>
                        <p class="text-gray-700 mb-6">
                            We may update our Privacy Policy from time to time. We will notify you of any changes by 
                            posting the new Privacy Policy on this page and updating the "Last Updated" date.
                        </p>

                        <h3 class="text-xl font-bold text-gray-900 mb-4">9. Contact Us</h3>
                        <p class="text-gray-700">
                            If you have questions about this Privacy Policy, please contact us at:<br>
                            <a href="mailto:privacy@quizball.com" class="text-blue-600 hover:text-blue-800">privacy@quizball.com</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
