<x-filament::widget>
    <x-filament::card>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <div class="text-sm font-semibold text-white mb-2">Lead Assignments</div>
                <div class="space-y-2">
                    @forelse($this->leaders['leads'] as $row)
                        <div class="flex justify-between text-sm text-slate-200">
                            <span>{{ $row['name'] }}</span>
                            <span>{{ $row['total'] }} leads</span>
                        </div>
                    @empty
                        <div class="text-xs text-slate-400">No data</div>
                    @endforelse
                </div>
            </div>
            <div>
                <div class="text-sm font-semibold text-white mb-2">Deals by Seller Lead</div>
                <div class="space-y-2">
                    @forelse($this->leaders['deals'] as $row)
                        <div class="flex justify-between text-sm text-slate-200">
                            <span>{{ $row['name'] }}</span>
                            <span>{{ $row['total'] }} deals</span>
                        </div>
                    @empty
                        <div class="text-xs text-slate-400">No data</div>
                    @endforelse
                </div>
            </div>
        </div>
    </x-filament::card>
</x-filament::widget>
