<x-filament-panels::page>
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js"></script>
    <script>
        document.addEventListener('livewire:initialized', function () {
            document.querySelectorAll('[data-deal-stage]').forEach(function (el) {
                Sortable.create(el, {
                    group: 'deals',
                    animation: 150,
                    ghostClass: 'kanban-ghost',
                    onEnd: function (evt) {
                        var dealId   = parseInt(evt.item.dataset.dealId);
                        var newStage = evt.to.dataset.dealStage;
                        @this.call('moveCard', dealId, newStage);
                    }
                });
            });
        });
    </script>
    <style>
        .kanban-ghost { opacity: 0.4; background: rgba(245, 158, 11, 0.15) !important; }
        .kanban-col { min-width: 340px; max-width: 340px; }
        .kanban-card { transition: all 0.15s ease; }
        .kanban-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.25); }
        .kanban-scroll::-webkit-scrollbar { height: 10px; }
        .kanban-scroll::-webkit-scrollbar-thumb { background: #475569; border-radius: 10px; }
        .kanban-scroll::-webkit-scrollbar-track { background: transparent; }
    </style>
    @endpush

    <div class="space-y-5">
        {{-- Header --}}
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h1 class="text-3xl font-bold text-white">Deal Pipeline</h1>
                <p class="text-sm text-gray-400 mt-1">Track deals by stage — orange border = stale (&gt;14 days)</p>
            </div>
            <x-filament::button
                tag="a"
                href="{{ route('filament.admin.resources.deals.create') }}"
                color="primary"
                icon="heroicon-o-plus"
                size="lg"
            >
                New Deal
            </x-filament::button>
        </div>

        {{-- Kanban board --}}
        <div class="flex gap-4 overflow-x-auto pb-4 kanban-scroll" style="min-height: 70vh;">
            @foreach ($this->columns as $stageKey => $column)
                @php
                    $stageColor = match($stageKey) {
                        'analyzing'      => 'from-blue-900 to-blue-800',
                        'contract_sent'  => 'from-purple-900 to-purple-800',
                        'under_contract' => 'from-amber-900 to-amber-800',
                        'clear_to_close' => 'from-emerald-900 to-emerald-800',
                        'closed_won'     => 'from-green-900 to-green-800',
                        'closed_lost'    => 'from-red-900 to-red-800',
                        default          => 'from-gray-900 to-gray-800',
                    };
                @endphp
                <div class="kanban-col flex-shrink-0 bg-gray-900/60 rounded-xl border border-gray-800 flex flex-col" style="max-height: 78vh;">

                    {{-- Column header --}}
                    <div class="px-4 py-3 border-b border-gray-800 rounded-t-xl bg-gradient-to-r {{ $stageColor }}">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-bold text-white uppercase tracking-wide">
                                {{ $column['label'] }}
                            </h3>
                            <span class="text-xs px-2.5 py-1 rounded-full bg-black/30 text-white font-semibold">
                                {{ $column['deals']->count() }}
                            </span>
                        </div>
                        @if($column['total'] > 0)
                            <div class="text-sm text-white/80 mt-1 font-semibold">
                                ${{ number_format($column['total']) }}
                            </div>
                        @endif
                    </div>

                    {{-- Sortable cards --}}
                    <div
                        class="p-3 space-y-3 overflow-y-auto flex-1"
                        data-deal-stage="{{ $stageKey }}"
                        style="min-height: 200px;"
                    >
                        @forelse ($column['deals'] as $deal)
                            @php
                                $daysInStage  = (int) now()->diffInDays($deal->updated_at);
                                $isStale      = $daysInStage > 14;
                                $dealValue    = $deal->sale_price ?? $deal->assignment_fee ?? $deal->purchase_price;
                                $propertyAddr = $deal->property?->address;
                                $profit       = $deal->profit;
                            @endphp

                            <div
                                class="kanban-card rounded-lg border-l-4 {{ $isStale ? 'border-l-orange-500' : 'border-l-blue-500' }} border border-gray-700 bg-gray-800 p-3.5 cursor-grab active:cursor-grabbing"
                                data-deal-id="{{ $deal->id }}"
                            >
                                {{-- Deal name --}}
                                <div class="font-bold text-white text-sm mb-2 truncate">
                                    {{ $deal->name }}
                                </div>

                                {{-- Property address --}}
                                @if($propertyAddr)
                                    <div class="flex items-center gap-1.5 text-xs text-gray-400 mb-2">
                                        <x-heroicon-s-home class="w-3.5 h-3.5 text-gray-500" />
                                        <span class="truncate">{{ $propertyAddr }}</span>
                                    </div>
                                @endif

                                {{-- Deal value + profit --}}
                                @if($dealValue)
                                    <div class="bg-gray-900/50 rounded px-2.5 py-1.5 mb-2">
                                        <div class="flex items-center justify-between">
                                            <span class="text-[10px] uppercase text-gray-500 font-semibold">Value</span>
                                            <span class="font-bold text-white text-sm">
                                                ${{ number_format((float)$dealValue) }}
                                            </span>
                                        </div>
                                        @if($profit)
                                            <div class="flex items-center justify-between mt-1">
                                                <span class="text-[10px] uppercase text-gray-500 font-semibold">Profit</span>
                                                <span class="font-bold text-green-400 text-sm">
                                                    ${{ number_format((float)$profit) }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                {{-- Stage time + closing date --}}
                                <div class="flex items-center justify-between mb-2">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold
                                        {{ $isStale ? 'bg-orange-500/20 text-orange-400 border border-orange-500/30' : 'bg-blue-500/20 text-blue-400 border border-blue-500/30' }}">
                                        @if($isStale)⚠ @endif{{ $daysInStage }}d in stage
                                    </span>

                                    @if($deal->closing_date)
                                        <span class="text-[10px] text-gray-400" title="Closing date">
                                            <x-heroicon-s-calendar class="w-3 h-3 inline" />
                                            {{ $deal->closing_date->format('M d') }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Quick note input --}}
                                <div class="pt-2 border-t border-gray-700/50">
                                    <div class="flex gap-1.5">
                                        <input
                                            type="text"
                                            placeholder="Quick note..."
                                            wire:model.defer="cardNotes.{{ $deal->id }}"
                                            class="flex-1 text-xs px-2.5 py-1.5 border border-gray-700 rounded bg-gray-900 text-gray-200 placeholder-gray-600 focus:outline-none focus:border-primary-500"
                                        />
                                        <button
                                            wire:click="saveNote({{ $deal->id }})"
                                            title="Save note"
                                            class="px-2.5 py-1.5 rounded bg-primary-500 text-white hover:bg-primary-600 transition-colors"
                                        >
                                            <x-heroicon-s-check class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>

                                {{-- View link --}}
                                <div class="pt-2 flex justify-end">
                                    <a
                                        href="{{ route('filament.admin.resources.deals.edit', $deal) }}"
                                        class="text-xs text-primary-400 hover:text-primary-300 font-semibold"
                                    >
                                        Edit Deal →
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="flex flex-col items-center justify-center py-8 text-center">
                                <x-heroicon-o-inbox class="w-8 h-8 text-gray-600 mb-2" />
                                <p class="text-xs text-gray-500">Drop deals here</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-filament-panels::page>
