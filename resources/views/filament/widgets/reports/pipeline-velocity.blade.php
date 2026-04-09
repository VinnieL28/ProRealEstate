<x-filament-widgets::widget>
    <x-filament::section heading="Pipeline Velocity — Avg Days in Stage" icon="heroicon-o-clock">
        <p class="text-xs text-gray-500 mb-3">Shows average days since last update for leads currently in each stage. Longer = leads are stalling.</p>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="border-b border-gray-700 text-xs uppercase text-gray-400">
                        <th class="px-4 py-2">Stage</th>
                        <th class="px-4 py-2 text-right">Leads in Stage</th>
                        <th class="px-4 py-2 text-right">Avg Days Sitting</th>
                        <th class="px-4 py-2">Velocity</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @foreach($stages as $stage)
                        <tr class="hover:bg-gray-800/30 transition">
                            <td class="px-4 py-2 font-medium text-gray-100">{{ $stage['label'] }}</td>
                            <td class="px-4 py-2 text-right text-gray-300">{{ $stage['count'] }}</td>
                            <td class="px-4 py-2 text-right font-semibold
                                {{ $stage['heat'] === 'slow' ? 'text-red-400' : ($stage['heat'] === 'medium' ? 'text-amber-400' : 'text-green-400') }}">
                                {{ $stage['avg_days'] !== null ? $stage['avg_days'] . 'd' : '—' }}
                            </td>
                            <td class="px-4 py-2">
                                @if($stage['heat'] === 'slow')
                                    <span class="inline-flex items-center gap-1 rounded px-2 py-0.5 text-xs bg-red-900/50 text-red-300">🐢 Slow</span>
                                @elseif($stage['heat'] === 'medium')
                                    <span class="inline-flex items-center gap-1 rounded px-2 py-0.5 text-xs bg-amber-900/50 text-amber-300">⚡ OK</span>
                                @elseif($stage['heat'] === 'fast')
                                    <span class="inline-flex items-center gap-1 rounded px-2 py-0.5 text-xs bg-green-900/50 text-green-300">✅ Fast</span>
                                @else
                                    <span class="text-gray-600">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
