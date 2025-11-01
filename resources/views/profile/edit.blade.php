<x-app-layout>
    <x-slot name="title">QuizBall - Λογαριασμός</x-slot>
    <x-slot name="header">
        <x-page-header title="Λογαριασμός" icon="👤" />
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Profile Card -->
            <div class="bg-white shadow-xl sm:rounded-2xl overflow-hidden">
                <div class="px-4 sm:px-8 pb-8">
                    <div class="mt-6 mb-6">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>
            </div>

            <!-- Password Card -->
            <div class="bg-white shadow-lg sm:rounded-2xl p-4 sm:p-8 border border-gray-200">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <!-- Delete Account Card -->
            <div class="bg-white shadow-lg sm:rounded-2xl p-4 sm:p-8 border border-red-200">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
