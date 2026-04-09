<x-filament::widget>
    <x-filament::card>
        <div class="text-lg font-semibold mb-2">Timeline</div>
        <div class="space-y-3">
            @forelse($this->items as $item)
                <div class="border border-gray-800 rounded-lg p-3 bg-gray-900/50">
                    <div class="flex justify-between text-xs text-slate-400">
                        <span class="uppercase tracking-wide">{{ $item['type'] }}</span>
                        <span>{{ \Illuminate\Support\Carbon::parse($item['time'])->diffForHumans() }}</span>
                    </div>
                    <div class="text-sm text-white font-semibold">{{ $item['title'] }}</div>
                    @if(!empty($item['body']))
                        <div class="text-sm text-slate-200">{{ \Illuminate\Support\Str::limit($item['body'], 180) }}</div>
                    @endif
                </div>
            @empty
                <div class="text-sm text-slate-400">No activity yet.</div>
            @endforelse
        </div>
    </x-filament::card>
</x-filament::widget>
