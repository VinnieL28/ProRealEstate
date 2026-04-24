<x-filament-panels::page>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="space-y-2">
            <h2 class="text-lg font-semibold text-white">Calls</h2>
            <div class="bg-gray-900/70 border border-gray-800 rounded-xl divide-y divide-gray-800">
                @forelse($calls as $call)
                    <div class="p-3">
                        <div class="flex justify-between text-sm text-slate-200">
                            <span>{{ $call['outcome'] }} • {{ \Illuminate\Support\Carbon::parse($call['called_at'])->diffForHumans() }}</span>
                            <span>{{ $call['duration_minutes'] }} min</span>
                        </div>
                        <div class="text-xs text-slate-400">
                            Lead: {{ $call['lead_id'] ?? '—' }} • User: {{ $call['user_id'] ?? '—' }}
                        </div>
                        @if(!empty($call['notes']))
                            <div class="text-xs text-slate-300 mt-1">{{ $call['notes'] }}</div>
                        @endif
                    </div>
                @empty
                    <div class="p-4 text-center text-slate-500">No calls found.</div>
                @endforelse
            </div>
        </div>
        <div class="space-y-2">
            <h2 class="text-lg font-semibold text-white">SMS</h2>
            <div class="bg-gray-900/70 border border-gray-800 rounded-xl divide-y divide-gray-800">
                @forelse($sms as $row)
                    <div class="p-3">
                        <div class="flex justify-between text-sm text-slate-200">
                            <span>{{ $row['direction'] }} • {{ \Illuminate\Support\Carbon::parse($row['sent_at'])->diffForHumans() }}</span>
                        </div>
                        <div class="text-xs text-slate-400">
                            Lead: {{ $row['lead_id'] ?? '—' }} • User: {{ $row['user_id'] ?? '—' }}
                        </div>
                        @if(!empty($row['message']))
                            <div class="text-xs text-slate-300 mt-1">{{ \Illuminate\Support\Str::limit($row['message'], 160) }}</div>
                        @endif
                    </div>
                @empty
                    <div class="p-4 text-center text-slate-500">No sms found.</div>
                @endforelse
            </div>
        </div>
    </div>
</x-filament-panels::page>
