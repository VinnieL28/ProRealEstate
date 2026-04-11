<x-filament::page>
    {{-- SortableJS via CDN --}}
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js"></script>
    <script>
        document.addEventListener('livewire:initialized', function () {
            document.querySelectorAll('[data-kanban-stage]').forEach(function (el) {
                Sortable.create(el, {
                    group: 'leads',
                    animation: 150,
                    ghostClass: 'opacity-40',
                    onEnd: function (evt) {
                        var leadId  = parseInt(evt.item.dataset.leadId);
                        var newStage = evt.to.dataset.kanbanStage;
                        @this.call('moveCard', leadId, newStage);
                    }
                });
            });
        });
    </script>
    @endpush

    <div class="space-y-4">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Lead Pipeline</h1>
            <x-filament::button
                tag="a"
                href="{{ route('filament.admin.resources.leads.create') }}"
                color="primary"
                icon="heroicon-o-plus"
            >
                New Lead
            </x-filament::button>
        </div>

        {{-- Legend --}}
        <div class="flex gap-4 text-xs text-gray-500 flex-wrap">
            <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded-full bg-green-400"></span>Score ≥ 70</span>
            <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded-full bg-yellow-400"></span>Score 40–69</span>
            <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded-full bg-red-400"></span>Score &lt; 40</span>
            <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded-full bg-gray-300"></span>Unscored</span>
        </div>

        {{-- Kanban board --}}
        <div class="flex gap-3 overflow-x-auto pb-4">
            @foreach ($this->columns as $stageKey => $column)
                <div class="flex-shrink-0 w-60 bg-gray-50 dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 flex flex-col max-h-[80vh]">

                    {{-- Column header --}}
                    <div class="px-3 py-2 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between rounded-t-xl bg-white dark:bg-gray-900">
                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                            {{ $column['label'] }}
                        </span>
                        <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                            {{ $column['leads']->count() }}
                        </span>
                    </div>

                    {{-- Sortable cards list --}}
                    <div
                        class="p-2 space-y-2 overflow-y-auto flex-1 min-h-[60px]"
                        data-kanban-stage="{{ $stageKey }}"
                    >
                        @forelse ($column['leads'] as $lead)
                            @php
                                $score = $lead->score;
                                $borderColor = match(true) {
                                    $score >= 70  => 'border-l-4 border-l-green-400',
                                    $score >= 40  => 'border-l-4 border-l-yellow-400',
                                    $score !== null => 'border-l-4 border-l-red-400',
                                    default        => 'border-l-4 border-l-gray-200',
                                };
                                $lastContact = $lead->updated_at;
                            @endphp

                            <div
                                class="rounded-lg border {{ $borderColor }} border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-xs space-y-1 cursor-grab active:cursor-grabbing shadow-xs hover:shadow-sm transition-shadow"
                                data-lead-id="{{ $lead->id }}"
                            >
                                {{-- Name --}}
                                <div class="font-semibold text-gray-800 dark:text-gray-100 truncate">
                                    {{ $lead->owner_name ?? ($lead->first_name . ' ' . $lead->last_name) ?? ('Lead #' . $lead->id) }}
                                </div>

                                {{-- Phone --}}
                                @if($lead->primary_phone ?? $lead->phone)
                                    <div class="text-gray-500 dark:text-gray-400 truncate">
                                        {{ $lead->primary_phone ?? $lead->phone }}
                                    </div>
                                @endif

                                {{-- Score badge + last contact --}}
                                <div class="flex items-center justify-between pt-1">
                                    @if($score !== null)
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold
                                            {{ $score >= 70 ? 'bg-green-100 text-green-700' : ($score >= 40 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                            {{ $score }}/100
                                        </span>
                                    @else
                                        <span class="text-[10px] text-gray-400">No score</span>
                                    @endif

                                    <span class="text-[10px] text-gray-400" title="{{ $lastContact?->format('M d, Y H:i') }}">
                                        {{ $lastContact?->diffForHumans() }}
                                    </span>
                                </div>

                                {{-- Quick actions --}}
                                <div class="flex items-center gap-1 pt-1 border-t border-gray-100 dark:border-gray-700">
                                    {{-- Log Call --}}
                                    <button
                                        wire:click="logCall({{ $lead->id }})"
                                        wire:loading.attr="disabled"
                                        title="Log Call"
                                        class="flex-1 flex items-center justify-center py-1 rounded text-[10px] text-gray-500 hover:bg-blue-50 hover:text-blue-600 transition-colors"
                                    >
                                        <x-heroicon-s-phone class="w-3 h-3" />
                                    </button>

                                    {{-- Send SMS --}}
                                    <button
                                        wire:click="sendQuickSms({{ $lead->id }})"
                                        wire:loading.attr="disabled"
                                        title="Send SMS"
                                        class="flex-1 flex items-center justify-center py-1 rounded text-[10px] text-gray-500 hover:bg-green-50 hover:text-green-600 transition-colors"
                                    >
                                        <x-heroicon-s-chat-bubble-left class="w-3 h-3" />
                                    </button>

                                    {{-- Set Appointment --}}
                                    <button
                                        wire:click="setAppointment({{ $lead->id }})"
                                        wire:loading.attr="disabled"
                                        title="Set Appointment"
                                        class="flex-1 flex items-center justify-center py-1 rounded text-[10px] text-gray-500 hover:bg-purple-50 hover:text-purple-600 transition-colors"
                                    >
                                        <x-heroicon-s-calendar class="w-3 h-3" />
                                    </button>

                                    {{-- View/Edit --}}
                                    <a
                                        href="{{ route('filament.admin.resources.leads.edit', $lead) }}"
                                        title="Edit Lead"
                                        class="flex-1 flex items-center justify-center py-1 rounded text-[10px] text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition-colors"
                                    >
                                        <x-heroicon-s-pencil class="w-3 h-3" />
                                    </a>
                                </div>
                            </div>
                        @empty
                            <p class="text-[11px] text-gray-400 italic py-2 text-center">
                                No leads
                            </p>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-filament::page>
