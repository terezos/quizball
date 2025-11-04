<nav x-data="{ open: false }" class="sticky top-0 z-50 backdrop-blur-sm bg-white border-b border-gray-200">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-6 py-2 sm:px-6 lg:px-8">
        <div class="flex @auth justify-between @else justify-center @endauth items-center h-30">
            <div class="flex items-center">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('welcome') }}" class="text-3xl p-1 font-black text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 transition-all duration-200">
                        quizball.io
                    </a>


                </div>

                <!-- Navigation Links -->
                @auth
                    <div class="hidden space-x-2 sm:ms-10 sm:flex items-center">
                        <x-nav-link :href="route('game.lobby')" :active="request()->routeIs('game.*')" icon="⚽">
                            Παιχνίδι
                        </x-nav-link>
                        <x-nav-link :href="route('statistics.index')" :active="request()->routeIs('statistics.*')" icon="📊">
                            Στατιστικά
                        </x-nav-link>
                        @if(auth()->user()->isEditor())
                            <x-nav-link :href="route('questions.index')" :active="request()->routeIs('questions.*')" icon="📝">
                                Ερωτήσεις
                            </x-nav-link>
                        @endif
                        @if(auth()->user()->isAdmin())
                            <x-nav-link :href="route('categories.index')" :active="request()->routeIs('categories.*')" icon="📂">
                                Κατηγορίες
                            </x-nav-link>
                            <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')" icon="👥">
                                Χρήστες
                            </x-nav-link>
                        @endif
                    </div>
                @endauth
            </div>

            <!-- Settings Dropdown -->
            @auth
            <div class="hidden sm:flex sm:items-center sm:ms-6 gap-4">
                <!-- Notification Bell -->
                <div x-data="{
                    open: false,
                    notifications: [],
                    unreadCount: 0,
                    async fetchNotifications() {
                        try {
                            const response = await fetch('{{ route('notifications.unread') }}');
                            const data = await response.json();
                            this.notifications = data.notifications;
                            this.unreadCount = data.unread_count;
                        } catch (error) {
                            console.error('Error fetching notifications:', error);
                        }
                    },
                    async markAsRead(notificationId) {
                        try {
                            await fetch(`/notifications/${notificationId}/read`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                                }
                            });
                            await this.fetchNotifications();
                        } catch (error) {
                            console.error('Error marking notification as read:', error);
                        }
                    },
                    async markAllAsRead() {
                        try {
                            await fetch('{{ route('notifications.markAllAsRead') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                                }
                            });
                            await this.fetchNotifications();
                        } catch (error) {
                            console.error('Error marking all as read:', error);
                        }
                    }
                }" x-init="fetchNotifications(); setInterval(() => fetchNotifications(), 30000)" class="relative">
                    <button @click="open = !open" class="relative p-2 text-slate-600 hover:text-indigo-600 transition-colors duration-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        <span x-text="unreadCount" class="absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white bg-rose-600 rounded-full min-w-[18px]"></span>
                    </button>

                    <!-- Dropdown -->
                    <div x-show="open" @click.outside="open = false" x-transition x-cloak class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-2xl border border-slate-200 overflow-hidden z-50">
                        <div class="p-3 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-slate-200">
                            <div class="flex items-center justify-between">
                                <h3 class="font-semibold text-slate-900">Notifications</h3>
                                <button @click="markAllAsRead()" x-show="unreadCount > 0" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                                    Mark all read
                                </button>
                            </div>
                        </div>

                        <div class="max-h-96 overflow-y-auto">
                            <template x-if="notifications.length === 0">
                                <div class="p-6 text-center text-slate-500 text-sm">
                                    No new notifications
                                </div>
                            </template>

                            <template x-for="notification in notifications" :key="notification.id">
                                <div @click="markAsRead(notification.id)" class="p-3 border-b border-slate-100 hover:bg-slate-50 cursor-pointer transition-colors duration-150">
                                    <div class="flex items-start gap-2">
                                        <div class="flex-1">
                                            <p class="text-sm font-semibold text-slate-900" x-text="notification.title"></p>
                                            <p class="text-xs text-slate-600 mt-0.5" x-text="notification.message"></p>
                                            <p class="text-xs text-slate-400 mt-1" x-text="new Date(notification.created_at).toLocaleString()"></p>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="p-2 bg-slate-50 border-t border-slate-200">
                            <a href="{{ route('notifications.index') }}" class="block text-center text-sm text-indigo-600 hover:text-indigo-800 font-medium py-2">
                                View all notifications
                            </a>
                        </div>
                    </div>
                </div>
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-3 px-1 py-1 border-2 border-transparent text-sm leading-4 font-semibold rounded-xl text-white bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-105">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center overflow-hidden">
                                    <img src="{{ \App\Avatar::showAvatar(Auth::user()) }}" alt="{{ Auth::user()->name }}" class="w-full h-full object-cover">
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
                <div class="flex items-center gap-2 sm:hidden">
                    <!-- Mobile Notification Bell -->
                    <div x-data="{
                        open: false,
                        notifications: [],
                        unreadCount: 0,
                        async fetchNotifications() {
                            try {
                                const response = await fetch('{{ route('notifications.unread') }}');
                                const data = await response.json();
                                this.notifications = data.notifications;
                                this.unreadCount = data.unread_count;
                            } catch (error) {
                                console.error('Error fetching notifications:', error);
                            }
                        },
                        async markAsRead(notificationId) {
                            try {
                                await fetch(`/notifications/${notificationId}/read`, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                                    }
                                });
                                await this.fetchNotifications();
                            } catch (error) {
                                console.error('Error marking notification as read:', error);
                            }
                        },
                        async markAllAsRead() {
                            try {
                                await fetch('{{ route('notifications.markAllAsRead') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                                    }
                                });
                                await this.fetchNotifications();
                            } catch (error) {
                                console.error('Error marking all as read:', error);
                            }
                        }
                    }"
                         x-init="fetchNotifications(); setInterval(() => fetchNotifications(), 30000)"
                         class="relative">
                        <button @click="open = !open" class="relative p-3 rounded-xl text-gray-600 hover:text-white hover:bg-gradient-to-r hover:from-blue-600 hover:to-purple-600 focus:outline-none focus:ring-2 focus:ring-purple-500 transition-all duration-200">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/>
                            </svg>
                            <span
                                  class="absolute top-1 right-1 inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-500 rounded-full"
                                  x-text="unreadCount > 9 ? '9+' : unreadCount"></span>
                        </button>

                        <!-- Mobile Notifications Dropdown -->
                        <div x-show="open"
                             @click.away="open = false"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="fixed right-2 top-16 w-[calc(100vw-1rem)] max-w-sm bg-white rounded-lg shadow-xl border border-gray-200 z-50"
                             style="display: none;">
                            <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                                <h3 class="text-lg font-semibold text-gray-900">Ειδοποιήσεις</h3>
                                <button @click="markAllAsRead()"
                                        x-show="unreadCount > 0"
                                        class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                    Όλες ως αναγνωσμένες
                                </button>
                            </div>
                            <div class="max-h-96 overflow-y-auto">
                                <template x-if="notifications.length === 0">
                                    <div class="p-8 text-center text-gray-500">
                                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/>
                                        </svg>
                                        <p class="text-sm">Δεν υπάρχουν νέες ειδοποιήσεις</p>
                                    </div>
                                </template>
                                <template x-for="notification in notifications" :key="notification.id">
                                    <div class="p-4 border-b border-gray-100 hover:bg-gray-50 transition cursor-pointer"
                                         :class="{ 'bg-blue-50': !notification.read_at }"
                                         @click="markAsRead(notification.id); window.location.href = notification.data.url || '#'">
                                        <div class="flex items-start gap-3">
                                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                <span x-text="notification.data.icon || '🔔'" class="text-xl"></span>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900" x-text="notification.data.title"></p>
                                                <p class="text-sm text-gray-600 mt-1" x-text="notification.data.message"></p>
                                                <p class="text-xs text-gray-400 mt-2" x-text="notification.created_at_human"></p>
                                            </div>
                                            <div x-show="!notification.read_at" class="flex-shrink-0">
                                                <span class="inline-block w-2 h-2 bg-blue-600 rounded-full"></span>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <div class="p-3 border-t border-gray-200 text-center">
                                <a href="{{ route('notifications.index') }}"
                                   class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                    Προβολή όλων
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Hamburger Button -->
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
                        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 sm:py-2 py-3">
                            <div class="flex items-center justify-between gap-4">
                                <div class="flex items-center gap-3 text-white">
                                    <svg class="w-6 h-6 md:w-8 md:h-8 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    <div>
                                        <div class="font-bold text-sm md:text-lg leading-tight">Γίνε Μέλος Δωρεάν &amp; Ξεκλείδωσε Όλα τα Χαρακτηριστικά!</div>
                                        <div class="text-xs md:text-sm text-blue-100 sm:block">Στατιστικά παιχνιδιών, Προσωπικό ιστορικό, Ιδιωτικά παιχνίδια</div>
                                    </div>
                                </div>

                                <div class="flex-shrink-0">
                                    <a href="{{ route('register') }}" class="sm:inline-flex lg:inline-flex items-center justify-center px-5 py-2.5 bg-white text-blue-600 font-bold rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                                        Σύνδεση
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endguest

    <!-- Responsive Navigation Menu -->
    <div x-show="open"
         @click.away="open = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-1"
         class="absolute top-full left-0 right-0 sm:hidden bg-white shadow-2xl border-b border-gray-200 z-40"
         style="display: none;">
        <div class="max-w-7xl mx-auto">
            <!-- Navigation Links -->
            @auth
            <div class="py-3 space-y-1">
                <a href="{{ route('dashboard') }}"
                   class="flex items-center gap-3 px-6 py-3 transition-colors {{ request()->routeIs('dashboard') ? 'bg-gradient-to-r from-blue-50 to-purple-50 border-l-4 border-blue-600 text-blue-600' : 'text-gray-700 hover:bg-gray-50' }}">
                    <svg class="w-6 h-6 {{ request()->routeIs('dashboard') ? 'text-blue-600' : 'text-gray-500' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                    </svg>
                    <span class="font-semibold">Αρχική</span>
                </a>

                <a href="{{ route('game.lobby') }}"
                   class="flex items-center gap-3 px-6 py-3 transition-colors {{ request()->routeIs('game.*') ? 'bg-gradient-to-r from-blue-50 to-purple-50 border-l-4 border-blue-600 text-blue-600' : 'text-gray-700 hover:bg-gray-50' }}">
                    <span class="text-2xl">⚽</span>
                    <span class="font-semibold">Παιχνίδι</span>
                </a>

                <a href="{{ route('statistics.index') }}"
                   class="flex items-center gap-3 px-6 py-3 transition-colors {{ request()->routeIs('statistics.*') ? 'bg-gradient-to-r from-blue-50 to-purple-50 border-l-4 border-blue-600 text-blue-600' : 'text-gray-700 hover:bg-gray-50' }}">
                    <span class="text-2xl">📊</span>
                    <span class="font-semibold">Στατιστικά</span>
                </a>

                @if(auth()->user()->isEditor())
                    <a href="{{ route('questions.index') }}"
                       class="flex items-center gap-3 px-6 py-3 transition-colors {{ request()->routeIs('questions.*') ? 'bg-gradient-to-r from-blue-50 to-purple-50 border-l-4 border-blue-600 text-blue-600' : 'text-gray-700 hover:bg-gray-50' }}">
                        <span class="text-2xl">📝</span>
                        <span class="font-semibold">Ερωτήσεις</span>
                    </a>
                @endif

                @if(auth()->user()->isAdmin())
                    <a href="{{ route('categories.index') }}"
                       class="flex items-center gap-3 px-6 py-3 transition-colors {{ request()->routeIs('categories.*') ? 'bg-gradient-to-r from-blue-50 to-purple-50 border-l-4 border-blue-600 text-blue-600' : 'text-gray-700 hover:bg-gray-50' }}">
                        <span class="text-2xl">📂</span>
                        <span class="font-semibold">Κατηγορίες</span>
                    </a>
                    <a href="{{ route('admin.users.index') }}"
                       class="flex items-center gap-3 px-6 py-3 transition-colors {{ request()->routeIs('admin.users.*') ? 'bg-gradient-to-r from-blue-50 to-purple-50 border-l-4 border-blue-600 text-blue-600' : 'text-gray-700 hover:bg-gray-50' }}">
                        <span class="text-2xl">👥</span>
                        <span class="font-semibold">Χρήστες</span>
                    </a>
                @endif
            </div>
            @endauth

            <!-- User Section -->
            @auth
            <div class="border-t border-gray-200 bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50">
                <!-- User Profile Card -->
                <div class="px-6 py-4">
                    <div class="flex items-center gap-3 p-4 bg-white rounded-2xl shadow-md">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-600 via-purple-600 to-pink-600 rounded-full flex items-center justify-center shadow-lg overflow-hidden">
                            <img src="{{ \App\Avatar::showAvatar(Auth::user()) }}" alt="{{ Auth::user()->name }}" class="w-full h-full object-cover">
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-bold text-base text-gray-900 truncate">{{ Auth::user()->name }}</div>
                            <div class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</div>
                        </div>
                    </div>
                </div>

                <!-- Menu Items -->
                <div class="pb-4 space-y-1">
                    <a href="{{ route('profile.edit') }}"
                       class="flex items-center gap-3 mx-4 px-4 py-3 rounded-xl transition-all hover:bg-white hover:shadow-md {{ request()->routeIs('profile.*') ? 'bg-white shadow-md' : '' }}">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-500 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <span class="font-semibold text-gray-900">Προφίλ</span>
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="w-full flex items-center gap-3 mx-4 px-4 py-3 rounded-xl transition-all hover:bg-red-50 hover:shadow-md text-left">
                            <div class="w-10 h-10 bg-gradient-to-br from-red-500 to-pink-500 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <span class="font-semibold text-gray-900">Αποσύνδεση</span>
                        </button>
                    </form>
                </div>
            </div>
            @endauth
        </div>
    </div>
</nav>
