<x-filament-widgets::widget>
    <x-filament::section icon="heroicon-o-funnel">
        <x-slot name="heading">Lead Conversion Funnel</x-slot>
        <x-slot name="description">From new leads to closed deals — where leads drop off</x-slot>

        @if($total === 0)
            <div class="flex items-center gap-3 py-4 px-4 rounded-lg bg-gray-800/50">
                <x-heroicon-o-chart-bar class="w-6 h-6 text-gray-500" />
                <p class="text-sm text-gray-400">No lead data yet. Add some leads to see your funnel.</p>
            </div>
        @else
            <div class="space-y-3 mt-2">
                @foreach($data as $row)
                    <div>
                        <div class="flex items-center justify-between mb-1.5 text-sm">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full" style="background: {{ $row['color'] }};"></span>
                                <span class="font-semibold text-gray-200">{{ $row['label'] }}</span>
                                @if($row['drop'] !== null && $row['drop'] > 0)
                                    <span class="text-xs text-red-400">↓ {{ $row['drop'] }}% drop</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-xs text-gray-500">{{ $row['percent'] }}%</span>
                                <span class="font-bold text-white tabular-nums">{{ number_format($row['count']) }}</span>
                            </div>
                        </div>
                        <div class="h-8 bg-gray-900/50 rounded overflow-hidden">
                            <div class="h-full rounded transition-all duration-500 flex items-center px-3"
                                 style="width: {{ $row['width'] }}%; background: linear-gradient(90deg, {{ $row['color'] }}cc, {{ $row['color'] }}66);">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
