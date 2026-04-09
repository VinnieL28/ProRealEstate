<x-filament::page>
    <div class="space-y-4">

        {{-- Connection status banner --}}
        @if(!$isConnected)
            <div class="rounded-xl border border-amber-600/40 bg-amber-950/30 p-4 text-sm text-amber-300 flex items-center gap-3">
                <x-heroicon-o-exclamation-triangle class="w-5 h-5 shrink-0" />
                <span>Gmail is not connected. Add your Google OAuth credentials in <a href="/admin/settings" class="underline">Settings</a>, then click <strong>Connect Gmail</strong> above.</span>
            </div>
        @else
            <div class="rounded-xl border border-green-600/40 bg-green-950/30 p-4 text-sm text-green-300 flex items-center gap-3">
                <x-heroicon-o-check-circle class="w-5 h-5 shrink-0" />
                <span>Gmail connected. Use <strong>Sync Inbox</strong> to pull new messages or <strong>Compose</strong> to send an email.</span>
            </div>
        @endif

        {{-- Email list --}}
        <div class="bg-gray-900/70 border border-gray-800 rounded-xl divide-y divide-gray-800">
            @forelse($emails as $mail)
                <div class="p-4 hover:bg-gray-800/40 transition">
                    <div class="flex justify-between items-start gap-4">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-slate-100 truncate">{{ $mail['subject'] ?? '(no subject)' }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">
                                From: <span class="text-slate-300">{{ $mail['from_address'] ?? '—' }}</span>
                                &nbsp;·&nbsp;
                                To: <span class="text-slate-300">{{ $mail['to_address'] ?? '—' }}</span>
                                &nbsp;·&nbsp;
                                <span class="capitalize">{{ $mail['direction'] }}</span>
                            </p>
                            @if(!empty($mail['body_preview']))
                                <p class="text-xs text-slate-400 mt-1 line-clamp-2">{{ \Illuminate\Support\Str::limit($mail['body_preview'], 220) }}</p>
                            @endif
                        </div>
                        <span class="text-xs text-slate-500 whitespace-nowrap shrink-0">
                            {{ \Illuminate\Support\Carbon::parse($mail['sent_at'])->diffForHumans() }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-slate-500">
                    <x-heroicon-o-inbox class="w-10 h-10 mx-auto mb-2 opacity-30" />
                    <p>No emails synced yet.</p>
                </div>
            @endforelse
        </div>

    </div>
</x-filament::page>
