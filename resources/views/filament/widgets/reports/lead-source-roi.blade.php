<x-filament-widgets::widget>
    <x-filament::section heading="Lead Source ROI" icon="heroicon-o-magnifying-glass-circle">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="border-b border-gray-700 text-xs uppercase text-gray-400">
                        <th class="px-4 py-2">Source</th>
                        <th class="px-4 py-2 text-right">Total Leads</th>
                        <th class="px-4 py-2 text-right">Closed Won</th>
                        <th class="px-4 py-2 text-right">Conversion</th>
                        <th class="px-4 py-2 text-right">Avg Profit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @forelse($rows as $row)
                        <tr class="hover:bg-gray-800/30 transition">
                            <td class="px-4 py-2 font-medium text-gray-100">{{ $row['source'] ?? '—' }}</td>
                            <td class="px-4 py-2 text-right text-gray-300">{{ $row['total'] }}</td>
                            <td class="px-4 py-2 text-right text-green-400 font-semibold">{{ $row['closed'] }}</td>
                            <td class="px-4 py-2 text-right">
                                @php $c = $row['conversion']; @endphp
                                <span class="inline-flex items-center rounded px-2 py-0.5 text-xs font-medium
                                    {{ $c >= 20 ? 'bg-green-900/60 text-green-300' : ($c >= 10 ? 'bg-amber-900/60 text-amber-300' : 'bg-gray-800 text-gray-400') }}">
                                    {{ $c }}%
                                </span>
                            </td>
                            <td class="px-4 py-2 text-right text-gray-300">
                                {{ $row['avg_profit'] !== null ? '$'.number_format($row['avg_profit'], 0) : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">No lead source data yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
