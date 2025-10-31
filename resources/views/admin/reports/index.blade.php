<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Question Reports" icon="🚩" />
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-slate-900 mb-2">Question Reports</h1>
                <p class="text-sm text-slate-600">Review and resolve user-submitted question reports</p>
            </div>

            <!-- Status Filter -->
            <div class="mb-6 flex gap-2">
                <a href="{{ route('reports.index') }}" class="px-4 py-2 rounded-lg text-sm font-medium {{ !request('status') ? 'bg-indigo-100 text-indigo-800' : 'bg-white text-slate-600 hover:bg-slate-50' }} border border-slate-200">
                    All
                </a>
                <a href="{{ route('reports.index', ['status' => 'pending']) }}" class="px-4 py-2 rounded-lg text-sm font-medium {{ request('status') === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-white text-slate-600 hover:bg-slate-50' }} border border-slate-200">
                    Pending
                </a>
                <a href="{{ route('reports.index', ['status' => 'resolved']) }}" class="px-4 py-2 rounded-lg text-sm font-medium {{ request('status') === 'resolved' ? 'bg-emerald-100 text-emerald-800' : 'bg-white text-slate-600 hover:bg-slate-50' }} border border-slate-200">
                    Resolved
                </a>
            </div>

            <!-- Reports List -->
            <div class="space-y-4">
                @forelse($reports as $report)
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                        <!-- Header -->
                        <div class="p-4 bg-gradient-to-r from-slate-50 to-gray-50 border-b border-slate-200">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $report->status === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                                            {{ ucfirst($report->status) }}
                                        </span>
                                        <span class="text-xs text-slate-500">
                                            Reported {{ $report->created_at->diffForHumans() }} by {{ $report->user ? $report->user->name : 'Guest' }}
                                        </span>
                                    </div>
                                    <h3 class="font-semibold text-slate-900 mb-1">{{ $report->question->question_text }}</h3>
                                    <div class="flex items-center gap-3 text-xs text-slate-600">
                                        <span>{{ $report->question->category->icon }} {{ $report->question->category->name }}</span>
                                        <span>•</span>
                                        <span class="capitalize">{{ $report->question->difficulty->value }}</span>
                                    </div>
                                </div>

                                @if($report->status === 'pending')
                                    <button type="button"
                                            onclick="if(confirm('Mark this report as resolved?')) {
                                                fetch('{{ route('reports.resolve', $report) }}', {
                                                    method: 'POST',
                                                    headers: {
                                                        'Content-Type': 'application/json',
                                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                                    }
                                                }).then(response => response.json())
                                                  .then(data => {
                                                      if(data.success) {
                                                          window.location.reload();
                                                      } else {
                                                          alert('Failed to resolve report');
                                                      }
                                                  }).catch(error => {
                                                      console.error('Error:', error);
                                                      alert('An error occurred');
                                                  });
                                            }"
                                            class="px-4 py-2 bg-gradient-to-r from-emerald-500 to-green-500 hover:from-emerald-600 hover:to-green-600 text-white text-sm font-medium rounded-lg transition-all duration-200 shadow-sm hover:shadow">
                                        Mark as Resolved
                                    </button>
                                @endif
                            </div>
                        </div>

                        <!-- Report Content -->
                        <div class="p-4">
                            <div class="mb-4">
                                <h4 class="text-sm font-semibold text-slate-700 mb-2">Report Reason:</h4>
                                <p class="text-sm text-slate-600 bg-slate-50 p-3 rounded-lg">{{ $report->reason }}</p>
                            </div>

                            <!-- Question Details -->
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <h4 class="text-sm font-semibold text-slate-700 mb-2">Question Details:</h4>
                                    <div class="text-xs text-slate-600 space-y-1">
                                        @if($report->question->created_by)
                                            <p><span class="font-medium">Created by:</span> {{ $report->question->creator->name }}</p>
                                        @endif
                                        @if($report->question->source_url)
                                            <p>
                                                <span class="font-medium">Source:</span>
                                                <a href="{{ $report->question->source_url }}" target="_blank" class="text-indigo-600 hover:text-indigo-800">
                                                    View Source →
                                                </a>
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                @if($report->status === 'resolved')
                                    <div>
                                        <h4 class="text-sm font-semibold text-slate-700 mb-2">Resolution:</h4>
                                        <div class="text-xs text-slate-600 space-y-1">
                                            <p><span class="font-medium">Resolved by:</span> {{ $report->resolver->name ?? 'N/A' }}</p>
                                            <p><span class="font-medium">Resolved at:</span> {{ $report->resolved_at?->format('M d, Y H:i') ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Actions -->
                            <div class="mt-4 pt-4 border-t border-slate-200 flex gap-2">
                                <a href="{{ route('questions.edit', $report->question) }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                                    Edit Question →
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-12 text-center">
                        <svg class="w-16 h-16 mx-auto text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="text-slate-500 font-medium">No reports found</p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($reports->hasPages())
                <div class="mt-6">
                    {{ $reports->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>