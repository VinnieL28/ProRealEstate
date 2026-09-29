<x-filament-widgets::widget>
    <div class="rounded-xl p-6 border border-gray-800 relative overflow-hidden"
         style="background: linear-gradient(135deg, rgba(245,158,11,0.12) 0%, rgba(15,23,42,0.95) 40%, rgba(15,23,42,1) 100%);">

        {{-- Decorative gradient blob --}}
        <div class="absolute top-0 right-0 w-64 h-64 rounded-full opacity-20 blur-3xl"
             style="background: radial-gradient(circle, #f59e0b 0%, transparent 70%); transform: translate(30%, -30%);"></div>

        {{-- Stacked on phones: a flex-1 (basis 0) text column next to the badges shrank to one word per line. --}}
        <div class="relative flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="min-w-0 md:flex-1">
                <div class="flex items-center gap-2 text-xs text-gray-400 mb-2">
                    <x-heroicon-m-calendar class="w-4 h-4" />
                    <span>{{ $today }}</span>
                </div>
                <h2 class="text-2xl md:text-3xl font-bold text-white mb-1">
                    {{ $greeting }}, {{ $firstName }}! 👋
                </h2>
                <p class="text-sm text-gray-400">
                    Here's what's happening with your pipeline today.
                </p>
            </div>

            <div class="flex gap-3 flex-wrap">
                @if($hotLeadsCount > 0)
                    <a href="{{ route('filament.admin.resources.leads.index') }}"
                       class="flex items-center gap-2 px-4 py-2.5 rounded-lg bg-red-500/10 border border-red-500/30 hover:bg-red-500/20 transition-colors">
                        <x-heroicon-s-fire class="w-5 h-5 text-red-400" />
                        <div>
                            <div class="text-lg font-bold text-red-400 leading-tight">{{ $hotLeadsCount }}</div>
                            <div class="text-[10px] text-red-300/80 uppercase tracking-wide">Hot Leads</div>
                        </div>
                    </a>
                @endif

                @if($tasksDueToday > 0)
                    <a href="{{ route('filament.admin.resources.tasks.index') }}"
                       class="flex items-center gap-2 px-4 py-2.5 rounded-lg bg-amber-500/10 border border-amber-500/30 hover:bg-amber-500/20 transition-colors">
                        <x-heroicon-s-clock class="w-5 h-5 text-amber-400" />
                        <div>
                            <div class="text-lg font-bold text-amber-400 leading-tight">{{ $tasksDueToday }}</div>
                            <div class="text-[10px] text-amber-300/80 uppercase tracking-wide">Due Today</div>
                        </div>
                    </a>
                @endif

                @if($overdueTasks > 0)
                    <a href="{{ route('filament.admin.resources.tasks.index') }}"
                       class="flex items-center gap-2 px-4 py-2.5 rounded-lg bg-red-600/10 border border-red-600/30 hover:bg-red-600/20 transition-colors">
                        <x-heroicon-s-exclamation-triangle class="w-5 h-5 text-red-500" />
                        <div>
                            <div class="text-lg font-bold text-red-500 leading-tight">{{ $overdueTasks }}</div>
                            <div class="text-[10px] text-red-400/80 uppercase tracking-wide">Overdue</div>
                        </div>
                    </a>
                @endif

                @if($hotLeadsCount === 0 && $tasksDueToday === 0 && $overdueTasks === 0)
                    <div class="flex items-center gap-2 px-4 py-2.5 rounded-lg bg-green-500/10 border border-green-500/30">
                        <x-heroicon-s-check-circle class="w-5 h-5 text-green-400" />
                        <div class="text-sm font-semibold text-green-400">All caught up! 🎉</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
