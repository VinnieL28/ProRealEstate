<x-filament::page>
    {{-- SortableJS --}}
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js"></script>
    <script>
        document.addEventListener('livewire:initialized', function () {
            document.querySelectorAll('[data-deal-stage]').forEach(function (el) {
                Sortable.create(el, {
                    group: 'deals',
                    animation: 150,
                    ghostClass: 'opacity-40',
                    onEnd: function (evt) {
                        var dealId   = parseInt(evt.item.dataset.dealId);
                        var newStage = evt.to.dataset.dealStage;
                        @this.call('moveCard', dealId, newStage);
                    }
                });
            });
        });
    </script>
    @endpush

    <div class="space-y-4">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Deal Pipeline</h1>
            <x-filament::button
                tag="a"
                href="{{ route('filament.admin.resources.deals.create') }}"
                color="primary"
                icon="heroicon-o-plus"
            >
                New Deal
            </x-filament::button>
        </div>

        {{-- Kanban board --}}
        <div class="flex gap-3 overflow-x-auto pb-4">
            @foreach ($this->columns as $stageKey => $column)
                <div class="flex-shrink-0 w-64 bg-gray-50 dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 flex flex-col max-h-[85vh]">

                    {{-- Column header --}}
                    <div class="px-3 py-2 border-b border-gray-200 dark:border-gray-700 rounded-t-xl bg-white dark:bg-gray-900">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                                {{ $column['label'] }}
                            </span>
                            <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                {{ $column['deals']->count() }}
                            </span>
                        </div>
                        @if($column['total'] > 0)
                            <div class="text-[11px] text-gray-400 mt-0.5">
                                ${{ number_format($column['total']) }}
                            </div>
                        @endif
                    </div>

                    {{-- Sortable cards --}}
                    <div
                        class="p-2 space-y-2 overflow-y-auto flex-1 min-h-[60px]"
                        data-deal-stage="{{ $stageKey }}"
                    >
                        @forelse ($column['deals'] as $deal)
                            @php
                                $daysInStage  = (int) now()->diffInDays($deal->updated_at);
                                $isStale      = $daysInStage > 14;
                                $dealValue    = $deal->sale_price ?? $deal->assignment_fee ?? $deal->purchase_price;
                                $propertyAddr = $deal->property?->address;
                            @endphp

                            <div
                                class="rounded-lg border {{ $isStale ? 'border-l-4 border-l-orange-400' : 'border-l-4 border-l-blue-400' }} border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-xs space-y-1 cursor-grab active:cursor-grabbing shadow-xs hover:shadow-sm transition-shadow"
                                data-deal-id="{{ $deal->id }}"
                            >
                                {{-- Deal name --}}
                                <div class="font-semibold text-gray-800 dark:text-gray-100 truncate">
                                    {{ $deal->name }}
                                </div>

                                {{-- Property address --}}
                                @if($propertyAddr)
                                    <div class="text-gray-500 dark:text-gray-400 truncate text-[11px]">
                                        {{ $propertyAddr }}
                                    </div>
                                @endif

                                {{-- Deal value --}}
                                @if($dealValue)
                                    <div class="font-bold text-gray-700 dark:text-gray-200">
                                        ${{ number_format((float)$dealValue) }}
                                    </div>
                                @endif

                                {{-- Days in stage badge --}}
                                <div class="flex items-center justify-between pt-1">
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-medium
                                        {{ $isStale ? 'bg-orange-100 text-orange-700' : 'bg-blue-50 text-blue-600' }}">
                                        @if($isStale)
                                            ⚠ {{ $daysInStage }}d
                                        @else
                                            {{ $daysInStage }}d
                                        @endif
                                    </span>

                                    @if($deal->closing_date)
                                        <span class="text-[10px] text-gray-400" title="Closing date">
                                            Close: {{ $deal->closing_date->format('M d') }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Quick note input --}}
                                <div class="pt-1 border-t border-gray-100 dark:border-gray-700">
                                    <div class="flex gap-1">
                                        <input
                                            type="text"
                                            placeholder="Quick note..."
                                            wire:model.defer="cardNotes.{{ $deal->id }}"
                                            class="flex-1 text-[10px] px-2 py-1 border border-gray-200 dark:border-gray-600 rounded bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:border-primary-400"
                                        />
                                        <button
                                            wire:click="saveNote({{ $deal->id }})"
                                            title="Save note"
                                            class="px-2 py-1 rounded bg-primary-500 text-white text-[10px] hover:bg-primary-600 transition-colors"
                                        >
                                            <x-heroicon-s-check class="w-3 h-3" />
                                        </button>
                                    </div>
                                </div>

                                {{-- View link --}}
                                <div class="pt-1 flex justify-end">
                                    <a
                                        href="{{ route('filament.admin.resources.deals.edit', $deal) }}"
                                        class="text-[10px] text-primary-600 hover:underline"
                                    >
                                        Edit →
                                    </a>
                                </div>
                            </div>
                        @empty
                            <p class="text-[11px] text-gray-400 italic py-2 text-center">No deals</p>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>

        <p class="text-[11px] text-gray-400">
            Orange border = stale (&gt;14 days since last update). Drag cards between columns to move stages.
        </p>
    </div>
</x-filament::page>
