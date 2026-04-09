<x-filament-widgets::widget>
    <x-filament::section heading="Agent Performance Comparison" icon="heroicon-o-trophy">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="border-b border-gray-700 text-xs uppercase text-gray-400">
                        <th class="px-4 py-2">Agent</th>
                        <th class="px-4 py-2">Role</th>
                        <th class="px-4 py-2 text-right">Leads</th>
                        <th class="px-4 py-2 text-right">Appts</th>
                        <th class="px-4 py-2 text-right">Won</th>
                        <th class="px-4 py-2 text-right">Lost</th>
                        <th class="px-4 py-2 text-right">Conv %</th>
                        <th class="px-4 py-2 text-right">Total Profit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @forelse($agents as $i => $agent)
                        <tr class="hover:bg-gray-800/30 transition">
                            <td class="px-4 py-2 font-medium text-gray-100 flex items-center gap-2">
                                @if($i === 0) <span class="text-amber-400">🥇</span>
                                @elseif($i === 1) <span class="text-gray-300">🥈</span>
                                @elseif($i === 2) <span class="text-amber-700">🥉</span>
                                @endif
                                {{ $agent['name'] }}
                            </td>
                            <td class="px-4 py-2 text-gray-400 text-xs capitalize">{{ str_replace('_', ' ', $agent['role']) }}</td>
                            <td class="px-4 py-2 text-right text-gray-300">{{ $agent['total_leads'] }}</td>
                            <td class="px-4 py-2 text-right text-blue-400">{{ $agent['appointments'] }}</td>
                            <td class="px-4 py-2 text-right text-green-400 font-semibold">{{ $agent['closed_won'] }}</td>
                            <td class="px-4 py-2 text-right text-red-400">{{ $agent['closed_lost'] }}</td>
                            <td class="px-4 py-2 text-right">
                                <span class="inline-flex items-center rounded px-2 py-0.5 text-xs font-medium
                                    {{ $agent['conversion'] >= 20 ? 'bg-green-900/60 text-green-300' : ($agent['conversion'] >= 10 ? 'bg-amber-900/60 text-amber-300' : 'bg-gray-800 text-gray-400') }}">
                                    {{ $agent['conversion'] }}%
                                </span>
                            </td>
                            <td class="px-4 py-2 text-right font-semibold text-amber-400">
                                ${{ number_format($agent['total_profit'], 0) }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-6 text-center text-gray-500">No agents found for this team.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
