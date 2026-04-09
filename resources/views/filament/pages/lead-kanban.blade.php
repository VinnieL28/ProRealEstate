<x-filament::page>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Lead Kanban</h1>

            <x-filament::button
                tag="a"
                href="{{ route('filament.admin.resources.leads.create') }}"
            >
                New Lead
            </x-filament::button>
        </div>

        {{-- Kanban columns --}}
        <div class="grid gap-4 md:grid-cols-4 xl:grid-cols-8">
            @foreach ($this->columns as $stageKey => $column)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 flex flex-col max-h-[80vh]">
                    {{-- Column header --}}
                    <div class="px-3 py-2 border-b flex items-center justify-between">
                        <span class="text-sm font-semibold">
                            {{ $column['label'] }}
                        </span>

                        <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">
                            {{ $column['leads']->count() }}
                        </span>
                    </div>

                    {{-- Cards list --}}
                    <div class="p-2 space-y-2 overflow-y-auto">
                        @forelse ($column['leads'] as $lead)
                            <div class="rounded-lg border border-gray-100 bg-gray-50 px-3 py-2 text-xs space-y-1">
                                <div class="font-semibold text-gray-800">
                                    {{ $lead->owner_name ?? $lead->name ?? ('Lead #' . $lead->id) }}
                                </div>

                                <div class="text-gray-500">
                                    {{ $lead->phone ?? $lead->primary_phone ?? $lead->primary_email }}
                                </div>

                                <div class="flex items-center justify-between pt-1">
                                    <span class="text-[11px] text-gray-400">
                                        {{ optional($lead->created_at)->diffForHumans() }}
                                    </span>

                                    <a
                                        href="{{ route('filament.admin.resources.leads.edit', $lead) }}"
                                        class="text-[11px] font-medium text-primary-600 hover:underline"
                                    >
                                        View
                                    </a>
                                </div>
                            </div>
                        @empty
                            <p class="text-[11px] text-gray-400 italic">
                                No leads in this stage.
                            </p>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>

        <p class="text-[11px] text-gray-400">
            * Versioni aktual është vetëm vizual (pa drag &amp; drop). Hapin tjetër mund të shtojmë
            lëvizjen e kartave midis kolonave dhe update automatik të stage në databazë.
        </p>
    </div>
</x-filament::page>
