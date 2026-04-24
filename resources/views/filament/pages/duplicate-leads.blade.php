<x-filament-panels::page>
    <div class="space-y-5">
        {{-- Toolbar --}}
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h2 class="text-xl font-bold text-white">Duplicate Detection</h2>
                <p class="text-sm text-gray-400 mt-0.5">
                    Find leads that share the same {{ $matchBy }} and merge them to keep your database clean.
                </p>
            </div>
            <div class="flex gap-2">
                <button wire:click="changeMatchBy('phone')"
                        class="px-4 py-2 rounded-lg text-sm font-semibold transition-colors
                        {{ $matchBy === 'phone' ? 'bg-primary-500 text-white' : 'bg-gray-800 text-gray-300 hover:bg-gray-700' }}">
                    Match by Phone
                </button>
                <button wire:click="changeMatchBy('email')"
                        class="px-4 py-2 rounded-lg text-sm font-semibold transition-colors
                        {{ $matchBy === 'email' ? 'bg-primary-500 text-white' : 'bg-gray-800 text-gray-300 hover:bg-gray-700' }}">
                    Match by Email
                </button>
                <button wire:click="scan"
                        class="px-4 py-2 rounded-lg text-sm font-semibold bg-gray-800 text-gray-300 hover:bg-gray-700 transition-colors">
                    ↻ Rescan
                </button>
            </div>
        </div>

        {{-- Results --}}
        @if(count($duplicates) === 0)
            <div class="flex items-center gap-3 py-5 px-5 rounded-lg bg-green-500/10 border border-green-500/20">
                <x-heroicon-s-check-circle class="w-8 h-8 text-green-400 flex-shrink-0" />
                <div>
                    <p class="text-base font-bold text-green-400">No duplicates found! 🎉</p>
                    <p class="text-sm text-gray-400 mt-0.5">
                        Your lead database is clean — no leads share the same {{ $matchBy }}.
                    </p>
                </div>
            </div>
        @else
            <div class="mb-3 text-sm text-amber-400">
                ⚠ Found <span class="font-bold">{{ count($duplicates) }}</span> group(s) of duplicate leads.
            </div>

            <div class="space-y-4">
                @foreach($duplicates as $group)
                    <div class="rounded-xl border border-gray-700 bg-gray-900/50 overflow-hidden">
                        <div class="px-4 py-3 bg-gradient-to-r from-red-900/30 to-transparent border-b border-gray-700 flex items-center justify-between">
                            <div>
                                <span class="text-xs uppercase text-gray-400 tracking-wide">Duplicate {{ $matchBy }}:</span>
                                <span class="ml-2 font-bold text-white font-mono">{{ $group['value'] }}</span>
                                <span class="ml-2 px-2 py-0.5 rounded text-xs bg-red-500/20 text-red-400 border border-red-500/30">
                                    {{ count($group['leads']) }} leads
                                </span>
                            </div>
                            <button
                                wire:click="mergeKeepFirst({{ json_encode(collect($group['leads'])->pluck('id')->toArray()) }})"
                                wire:confirm="Keep the first lead and delete the others?"
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-amber-600 text-white hover:bg-amber-500 transition-colors">
                                Merge (keep oldest)
                            </button>
                        </div>

                        <div class="divide-y divide-gray-800">
                            @foreach($group['leads'] as $i => $lead)
                                <div class="flex items-center gap-4 p-4 hover:bg-gray-800/50 transition">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary-500 to-red-500 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                                        {{ strtoupper(substr($lead['name'], 0, 1)) }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="font-semibold text-white truncate">{{ $lead['name'] }}</span>
                                            @if($i === 0)
                                                <span class="text-[10px] uppercase px-1.5 py-0.5 rounded bg-green-500/20 text-green-400 border border-green-500/30 font-bold">KEEP</span>
                                            @else
                                                <span class="text-[10px] uppercase px-1.5 py-0.5 rounded bg-red-500/20 text-red-400 border border-red-500/30 font-bold">DELETE</span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-400 mt-0.5 flex gap-3 flex-wrap">
                                            <span>📞 {{ $lead['phone'] }}</span>
                                            <span>📧 {{ $lead['email'] }}</span>
                                            <span class="capitalize">{{ str_replace('_', ' ', $lead['stage']) }}</span>
                                            @if($lead['score'] !== null)
                                                <span>Score: {{ $lead['score'] }}</span>
                                            @endif
                                            <span>Added {{ $lead['created_at'] }}</span>
                                        </div>
                                    </div>
                                    <a href="{{ $lead['edit_url'] }}"
                                       class="text-xs text-primary-400 hover:text-primary-300 font-semibold">
                                        View →
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-filament-panels::page>
