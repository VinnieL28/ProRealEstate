<x-filament-panels::page>
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js"></script>
    <script>
        document.addEventListener('livewire:initialized', function () {
            document.querySelectorAll('[data-kanban-stage]').forEach(function (el) {
                Sortable.create(el, {
                    group: 'leads',
                    animation: 150,
                    ghostClass: 'kanban-ghost',
                    onEnd: function (evt) {
                        var leadId   = parseInt(evt.item.dataset.leadId);
                        var newStage = evt.to.dataset.kanbanStage;
                        @this.call('moveCard', leadId, newStage);
                    }
                });
            });
        });
    </script>
    <style>
        .kanban-ghost { opacity: 0.4; background: rgba(245, 158, 11, 0.15) !important; }
        .kanban-col { min-width: 320px; max-width: 320px; }
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
                <h1 class="text-3xl font-bold text-white">Lead Pipeline</h1>
                <p class="text-sm text-gray-400 mt-1">Drag leads across stages — scores color-code priority</p>
            </div>
            <x-filament::button
                tag="a"
                href="{{ route('filament.admin.resources.leads.create') }}"
                color="primary"
                icon="heroicon-o-plus"
                size="lg"
            >
                New Lead
            </x-filament::button>
        </div>

        {{-- Legend --}}
        <div class="flex gap-6 text-xs text-gray-400 flex-wrap bg-gray-900/50 border border-gray-800 rounded-lg px-4 py-2.5">
            <span class="flex items-center gap-2"><span class="inline-block w-3 h-3 rounded-full bg-green-500"></span>Hot (Score ≥ 70)</span>
            <span class="flex items-center gap-2"><span class="inline-block w-3 h-3 rounded-full bg-yellow-500"></span>Warm (40–69)</span>
            <span class="flex items-center gap-2"><span class="inline-block w-3 h-3 rounded-full bg-red-500"></span>Cold (&lt; 40)</span>
            <span class="flex items-center gap-2"><span class="inline-block w-3 h-3 rounded-full bg-gray-500"></span>Unscored</span>
        </div>

        {{-- Kanban board --}}
        <div class="flex gap-4 overflow-x-auto pb-4 kanban-scroll" style="min-height: 70vh;">
            @foreach ($this->columns as $stageKey => $column)
                <div class="kanban-col flex-shrink-0 bg-gray-900/60 rounded-xl border border-gray-800 flex flex-col" style="max-height: 78vh;">

                    {{-- Column header --}}
                    <div class="px-4 py-3 border-b border-gray-800 flex items-center justify-between rounded-t-xl bg-gradient-to-r from-gray-900 to-gray-800">
                        <div>
                            <h3 class="text-sm font-bold text-white uppercase tracking-wide">
                                {{ $column['label'] }}
                            </h3>
                        </div>
                        <span class="text-xs px-2.5 py-1 rounded-full bg-gray-700 text-gray-200 font-semibold">
                            {{ $column['leads']->count() }}
                        </span>
                    </div>

                    {{-- Sortable cards list --}}
                    <div
                        class="p-3 space-y-3 overflow-y-auto flex-1"
                        data-kanban-stage="{{ $stageKey }}"
                        style="min-height: 200px;"
                    >
                        @forelse ($column['leads'] as $lead)
                            @php
                                $score = $lead->score;
                                $borderColor = match(true) {
                                    $score >= 70   => 'border-l-green-500',
                                    $score >= 40   => 'border-l-yellow-500',
                                    $score !== null => 'border-l-red-500',
                                    default        => 'border-l-gray-600',
                                };
                                $scoreBadge = match(true) {
                                    $score >= 70   => 'bg-green-500/20 text-green-400 border border-green-500/30',
                                    $score >= 40   => 'bg-yellow-500/20 text-yellow-400 border border-yellow-500/30',
                                    $score !== null => 'bg-red-500/20 text-red-400 border border-red-500/30',
                                    default        => 'bg-gray-700/50 text-gray-400 border border-gray-600',
                                };
                                $lastContact = $lead->updated_at;
                                $name = $lead->owner_name ?? trim(($lead->first_name ?? '') . ' ' . ($lead->last_name ?? '')) ?: 'Lead #' . $lead->id;
                            @endphp

                            <div
                                class="kanban-card rounded-lg border-l-4 {{ $borderColor }} border border-gray-700 bg-gray-800 p-3.5 cursor-grab active:cursor-grabbing"
                                data-lead-id="{{ $lead->id }}"
                            >
                                {{-- Name + Score --}}
                                <div class="flex items-start justify-between gap-2 mb-2">
                                    <div class="font-bold text-white text-sm truncate flex-1">
                                        {{ $name }}
                                    </div>
                                    @if($score !== null)
                                        <span class="px-2 py-0.5 rounded text-[11px] font-bold {{ $scoreBadge }}">
                                            {{ $score }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Contact info --}}
                                @if($lead->primary_phone ?? $lead->phone)
                                    <div class="flex items-center gap-1.5 text-xs text-gray-400 mb-1">
                                        <x-heroicon-s-phone class="w-3.5 h-3.5 text-gray-500" />
                                        <span class="truncate">{{ $lead->primary_phone ?? $lead->phone }}</span>
                                    </div>
                                @endif
                                @if($lead->email)
                                    <div class="flex items-center gap-1.5 text-xs text-gray-400 mb-2">
                                        <x-heroicon-s-envelope class="w-3.5 h-3.5 text-gray-500" />
                                        <span class="truncate">{{ $lead->email }}</span>
                                    </div>
                                @endif

                                {{-- Source + Last contact --}}
                                <div class="flex items-center justify-between pt-2 mb-2 border-t border-gray-700/50">
                                    @if($lead->lead_source)
                                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-gray-700 text-gray-300">
                                            {{ $lead->lead_source }}
                                        </span>
                                    @else
                                        <span class="text-[10px] text-gray-500">—</span>
                                    @endif
                                    <span class="text-[10px] text-gray-500" title="{{ $lastContact?->format('M d, Y H:i') }}">
                                        {{ $lastContact?->diffForHumans() }}
                                    </span>
                                </div>

                                {{-- Quick actions --}}
                                <div class="flex items-center gap-1 pt-2 border-t border-gray-700/50">
                                    <button
                                        wire:click="logCall({{ $lead->id }})"
                                        wire:loading.attr="disabled"
                                        title="Log Call"
                                        class="flex-1 flex items-center justify-center py-1.5 rounded text-xs text-gray-400 hover:bg-blue-500/20 hover:text-blue-400 transition-colors"
                                    >
                                        <x-heroicon-s-phone class="w-4 h-4" />
                                    </button>
                                    <button
                                        wire:click="sendQuickSms({{ $lead->id }})"
                                        wire:loading.attr="disabled"
                                        title="Send SMS"
                                        class="flex-1 flex items-center justify-center py-1.5 rounded text-xs text-gray-400 hover:bg-green-500/20 hover:text-green-400 transition-colors"
                                    >
                                        <x-heroicon-s-chat-bubble-left class="w-4 h-4" />
                                    </button>
                                    <button
                                        wire:click="setAppointment({{ $lead->id }})"
                                        wire:loading.attr="disabled"
                                        title="Set Appointment"
                                        class="flex-1 flex items-center justify-center py-1.5 rounded text-xs text-gray-400 hover:bg-purple-500/20 hover:text-purple-400 transition-colors"
                                    >
                                        <x-heroicon-s-calendar class="w-4 h-4" />
                                    </button>
                                    <a
                                        href="{{ route('filament.admin.resources.leads.edit', $lead) }}"
                                        title="Edit Lead"
                                        class="flex-1 flex items-center justify-center py-1.5 rounded text-xs text-gray-400 hover:bg-primary-500/20 hover:text-primary-400 transition-colors"
                                    >
                                        <x-heroicon-s-pencil class="w-4 h-4" />
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="flex flex-col items-center justify-center py-8 text-center">
                                <x-heroicon-o-inbox class="w-8 h-8 text-gray-600 mb-2" />
                                <p class="text-xs text-gray-500">Drop leads here</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-filament-panels::page>
