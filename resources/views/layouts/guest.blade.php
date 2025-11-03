<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            body { font-family: 'Inter', sans-serif; }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-white/80 from-indigo-50 via-white to-purple-50">
            <div class="mb-6">
                <a href="/">
                    <video class="media" muted="" autoplay="" loop="" preload="auto" width="auto" height="auto" playsinline="" style="max-width: 200px;">
                        <source src="/storage/logo/quizball.mp4" type="video/mp4">
                    </video>
                </a>
            </div>

            <div class="w-full sm:max-w-md px-8 py-8 bg-white shadow-xl overflow-hidden rounded-2xl border-2 border-gray-100">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
