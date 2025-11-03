<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Notifications" icon="🔔" />
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-slate-900">All Notifications</h1>
                @if($notifications->where('read_at', null)->count() > 0)
                    <form method="POST" action="{{ route('notifications.markAllAsRead') }}">
                        @csrf
                        <button type="submit" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                            Mark all as read
                        </button>
                    </form>
                @endif
            </div>

            <!-- Notifications Table -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                @if($notifications->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gradient-to-r from-slate-50 to-gray-50 border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Title</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Message</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Date</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-700 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @foreach($notifications as $notification)
                                    <tr class="hover:bg-slate-50 transition-colors duration-150 {{ is_null($notification->read_at) ? 'bg-indigo-50/30' : '' }}">
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            @if(is_null($notification->read_at))
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                                    New
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-green-600">
                                                    Viewed
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="font-semibold text-sm text-slate-900">{{ $notification->title }}</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="text-sm text-slate-600">{{ $notification->message }}</div>
                                            @if($notification->type === 'question_report' && $notification->data && auth()->user()->isEditor())
                                                <a href="{{ route('reports.index') }}" class="inline-block mt-1 text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                                                    Δες την αναφορά →
                                                </a>
                                            @endif
                                            @if($notification->type === 'question' && $notification->data && auth()->user()->isEditor())
                                                <a href="{{ route('questions.edit', $notification->data['question_id']) }}" class="inline-block mt-1 text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                                                    Δες την ερώτηση →
                                                </a>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div class="text-xs text-slate-600">{{ $notification->created_at->format('M d, Y') }}</div>
                                            <div class="text-xs text-slate-400">{{ $notification->created_at->format('H:i') }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-center whitespace-nowrap">
                                            @if(is_null($notification->read_at))
                                                <form method="POST" action="{{ route('notifications.markAsRead', $notification) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                                                        Mark as read
                                                    </button>
                                                </form>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-12 text-center">
                        <svg class="w-16 h-16 mx-auto text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        <p class="text-slate-500 font-medium">No notifications yet</p>
                    </div>
                @endif
            </div>

            <!-- Pagination -->
            @if($notifications->hasPages())
                <div class="mt-6">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
