<x-filament-widgets::widget>
    <x-filament::section icon="heroicon-o-fire">
        <x-slot name="heading">
            <span class="flex items-center gap-2">
                🔥 Hot Leads Needing Follow-Up
                <span class="inline-flex items-center rounded-full bg-red-900/60 px-2 py-0.5 text-xs font-semibold text-red-300">
                    {{ count($leads) }}
                </span>
            </span>
        </x-slot>

        <p class="text-xs text-gray-500 mb-3">
            Leads with motivation ≥ 4 that haven't been touched in 3+ days. Act now before they go cold.
        </p>

        @if(count($leads) === 0)
            <div class="flex items-center gap-3 py-3 px-4 rounded-lg bg-green-500/10 border border-green-500/20">
                <x-heroicon-s-check-circle class="w-6 h-6 text-green-400 flex-shrink-0" />
                <div>
                    <p class="text-sm font-semibold text-green-400">All hot leads are up to date 🎉</p>
                    <p class="text-xs text-gray-400 mt-0.5">No motivated leads have gone stale. Great work!</p>
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="border-b border-gray-700 text-xs uppercase text-gray-400">
                            <th class="px-3 py-2">Lead</th>
                            <th class="px-3 py-2">Phone</th>
                            <th class="px-3 py-2">Stage</th>
                            <th class="px-3 py-2 text-center">Motivation</th>
                            <th class="px-3 py-2 text-center">Score</th>
                            <th class="px-3 py-2">Timeline</th>
                            <th class="px-3 py-2">Assigned</th>
                            <th class="px-3 py-2">Last Touch</th>
                            <th class="px-3 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800">
                        @foreach($leads as $lead)
                            <tr class="hover:bg-red-950/20 transition">
                                <td class="px-3 py-2 font-medium text-gray-100">{{ $lead['name'] }}</td>
                                <td class="px-3 py-2 text-gray-400 font-mono text-xs">{{ $lead['phone'] }}</td>
                                <td class="px-3 py-2">
                                    <span class="rounded px-1.5 py-0.5 text-xs bg-gray-800 text-gray-300 capitalize">
                                        {{ str_replace('_', ' ', $lead['stage']) }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-center">
                                    @php $m = (int) $lead['motivation']; @endphp
                                    <span class="inline-flex items-center justify-center rounded-full w-7 h-7 text-xs font-bold
                                        {{ $m >= 5 ? 'bg-red-700 text-white' : ($m === 4 ? 'bg-orange-700 text-white' : 'bg-amber-800 text-white') }}">
                                        {{ $m }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-center">
                                    @php $s = $lead['hot_score']; @endphp
                                    <span class="inline-flex items-center rounded px-2 py-0.5 text-xs font-semibold
                                        {{ $s >= 8 ? 'bg-red-900/60 text-red-300' : 'bg-orange-900/60 text-orange-300' }}">
                                        {{ number_format($s, 1) }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-xs text-gray-400 capitalize">
                                    {{ $lead['sell_timeline'] ? str_replace('_', ' ', $lead['sell_timeline']) : '—' }}
                                </td>
                                <td class="px-3 py-2 text-gray-400 text-xs">{{ $lead['assigned_to'] }}</td>
                                <td class="px-3 py-2 text-red-400 text-xs font-medium">{{ $lead['last_touch'] }}</td>
                                <td class="px-3 py-2">
                                    <a href="{{ $lead['edit_url'] }}"
                                       class="inline-flex items-center gap-1 rounded px-2 py-1 text-xs bg-amber-600 hover:bg-amber-500 text-white transition">
                                        Follow Up
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
