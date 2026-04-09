<x-filament::page>
    <div class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-4 gap-4">
        @foreach($columns as $stage => $column)
            <div class="bg-gray-900/70 border border-gray-800 rounded-xl p-3" x-data
                 x-init="
                    if (window.Sortable) {
                        Sortable.create($refs.{{ $stage }}Cards, {
                            group: 'leads',
                            animation: 150,
                            onEnd: (event) => {
                                const leadId = event.item.dataset.id;
                                const newStage = event.to.dataset.stage;
                                $wire.updateStage(leadId, newStage);
                            },
                        });
                    }
                 ">
                <div class="flex items-center justify-between mb-2">
                    <div class="text-sm font-semibold text-slate-200">{{ $column['label'] }}</div>
                    <div class="text-xs text-slate-400">{{ count($column['items']) }} leads</div>
                </div>
                <div class="space-y-3" data-stage="{{ $stage }}" x-ref="{{ $stage }}Cards">
                    @forelse($column['items'] as $item)
                        <div class="bg-slate-800/80 border border-slate-700 rounded-lg p-3 shadow" data-id="{{ $item['id'] }}">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <div class="text-sm font-semibold text-white">{{ $item['owner_name'] ?? 'Unnamed' }}</div>
                                    <div class="text-xs text-slate-400">{{ $item['email'] }}</div>
                                    <div class="text-xs text-slate-400">{{ $item['phone'] }}</div>
                                </div>
                                <x-filament::badge color="primary" size="sm">{{ $item['lead_source'] ?? 'Unknown' }}</x-filament::badge>
                            </div>
                            <div class="mt-2 text-xs text-slate-400">
                                <span class="font-semibold text-slate-300">Motivation:</span> {{ $item['motivation'] ?? '—' }}
                            </div>
                            @if(!empty($item['notes']))
                                <div class="mt-2 text-xs text-slate-300 line-clamp-2">{{ $item['notes'] }}</div>
                            @endif
                            <div class="mt-2 text-[11px] text-slate-500 flex justify-between">
                                <span>Updated {{ $item['updated_at'] }}</span>
                                <span>Created {{ $item['created_at'] }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-xs text-slate-500 border border-dashed border-slate-700 rounded-lg p-3 text-center">
                            No leads yet.
                        </div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>

    @once
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    @endonce
</x-filament::page>
