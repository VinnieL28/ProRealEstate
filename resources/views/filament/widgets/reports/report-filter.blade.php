<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <span class="flex items-center gap-2 text-sm font-semibold">
                <x-heroicon-o-funnel class="w-4 h-4 text-amber-400" />
                Report Filters
            </span>
        </x-slot>

        <form wire:submit.prevent="applyFilter" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[200px]">
                {{ $this->form }}
            </div>
            <button
                type="submit"
                class="inline-flex items-center gap-1 rounded-lg bg-amber-500 hover:bg-amber-400 text-white px-4 py-2 text-sm font-medium transition"
            >
                Apply
            </button>
        </form>
        <p class="mt-2 text-xs text-gray-500">
            Currently showing:
            @php $from = session('report_date_from'); $until = session('report_date_until'); @endphp
            {{ $from ? \Carbon\Carbon::parse($from)->format('M j, Y') : '—' }}
            →
            {{ $until ? \Carbon\Carbon::parse($until)->format('M j, Y') : '—' }}
        </p>
    </x-filament::section>
</x-filament-widgets::widget>
