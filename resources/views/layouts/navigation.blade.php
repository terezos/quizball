<nav x-data="{ open: false }" class="sticky top-0 z-50 backdrop-blur-sm bg-white/80 border-b border-gray-200">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex @auth justify-between @else justify-center @endauth items-center h-16">
            <div class="flex items-center">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="text-2xl font-black text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 transition-all duration-200">
                        QuizBall
                    </a>
                </div>

                <!-- Navigation Links -->
                @auth
                    <div class="hidden space-x-1 sm:ms-10 sm:flex items-center">
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="group">
                        <span class="flex items-center gap-2">
                            <span class="font-semibold">Αρχική</span>
                        </span>
                        </x-nav-link>
                        <x-nav-link :href="route('game.lobby')" :active="request()->routeIs('game.*')" class="group">
                        <span class="flex items-center gap-2">
                            <span class="font-semibold">Παιχνίδι</span>
                        </span>
                        </x-nav-link>
                        @auth
                            @if(auth()->user()->isEditor())
                                <x-nav-link :href="route('questions.index')" :active="request()->routeIs('questions.*')"
                                            class="group">
                                <span class="flex items-center gap-2">
                                    <span class="font-semibold">Ερωτήσεις</span>
                                </span>
                                </x-nav-link>
                            @endif
                            @if(auth()->user()->isAdmin())
                                <x-nav-link :href="route('categories.index')"
                                            :active="request()->routeIs('categories.*')" class="group">
                                <span class="flex items-center gap-2">
                                    <span class="font-semibold">Κατηγορίες</span>
                                </span>
                                </x-nav-link>
                            @endif
                        @endauth
                    </div>
                @endauth
            </div>

            <!-- Settings Dropdown -->
            @auth
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-3 px-5 py-2.5 border-2 border-transparent text-sm leading-4 font-semibold rounded-xl text-white bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-105">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <span>{{ Auth::user()->name }}</span>
                            </div>
                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                </svg>
                                <span>Προφίλ</span>
                            </span>
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>Αποσύνδεση</span>
                                </span>
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>
            @endauth

            <!-- Hamburger -->
            @auth
                <div class="-me-2 flex items-center sm:hidden">
                    <button @click="open = ! open"
                            class="inline-flex items-center justify-center p-3 rounded-xl text-gray-600 hover:text-white hover:bg-gradient-to-r hover:from-blue-600 hover:to-purple-600 focus:outline-none focus:ring-2 focus:ring-purple-500 transition-all duration-200">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex"
                                  stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 6h16M4 12h16M4 18h16"/>
                            <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden"
                                  stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            @endauth
        </div>
    </div>

    <!-- Guest Promo Banner -->
    @guest
    <div class="bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-600 border-b border-blue-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-4 text-white">
                    <svg class="w-8 h-8 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    <div>
                        <div class="font-bold text-lg">Γίνε Μέλος & Ξεκλείδωσε Όλα τα Χαρακτηριστικά!</div>
                        <div class="text-sm text-blue-100">Παρακολούθηση προόδου • Leaderboards • Στατιστικά παιχνιδιών • Προσωπικό ιστορικό</div>
                    </div>
                </div>
                <div class="flex gap-3 flex-shrink-0">
                    <a href="{{ route('register') }}" class="px-6 py-2.5 bg-white hover:bg-gray-100 text-blue-600 font-bold rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                        Εγγραφή Δωρεάν
                    </a>
                    <a href="{{ route('login') }}" class="px-6 py-2.5 bg-transparent border-2 border-white hover:bg-white/10 text-white font-semibold rounded-xl transition-all duration-200">
                        Σύνδεση
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endguest

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-white/95 backdrop-blur-lg border-t border-gray-200">
        <div class="pt-2 pb-3 space-y-1">
            @auth
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                <span class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                    </svg>
                    <span>Αρχική</span>
                </span>
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('game.lobby')" :active="request()->routeIs('game.*')">
                <span class="flex items-center gap-2">
                    <span class="text-xl">⚽</span>
                    <span>Παιχνίδι</span>
                </span>
            </x-responsive-nav-link>
            @endauth
            @auth
                @if(auth()->user()->isEditor())
                    <x-responsive-nav-link :href="route('questions.index')" :active="request()->routeIs('questions.*')">
                        <span class="flex items-center gap-2">
                            <span class="text-xl">📝</span>
                            <span>Ερωτήσεις</span>
                        </span>
                    </x-responsive-nav-link>
                @endif
                @if(auth()->user()->isAdmin())
                    <x-responsive-nav-link :href="route('categories.index')" :active="request()->routeIs('categories.*')">
                        <span class="flex items-center gap-2">
                            <span class="text-xl">📂</span>
                            <span>Κατηγορίες</span>
                        </span>
                    </x-responsive-nav-link>
                @endif
            @endauth
        </div>

        <!-- Responsive Settings Options -->
        @auth
        <div class="pt-4 pb-4 border-t border-gray-200 bg-gradient-to-r from-blue-50 to-purple-50">
            <div class="px-4 mb-3">
                <div class="flex items-center gap-3 p-3 bg-white rounded-xl shadow-sm">
                    <div class="w-10 h-10 bg-gradient-to-r from-blue-600 to-purple-600 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <div class="font-bold text-base text-gray-800">{{ Auth::user()->name }}</div>
                        <div class="font-medium text-xs text-gray-500">{{ Auth::user()->email }}</div>
                    </div>
                </div>
            </div>

            <div class="space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                        </svg>
                        <span>Προφίλ</span>
                    </span>
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        <span class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"/>
                            </svg>
                            <span>Αποσύνδεση</span>
                        </span>
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
        @endauth
    </div>
</nav>
