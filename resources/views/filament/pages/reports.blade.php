<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Revenue line chart (full width) --}}
        @livewire(\App\Filament\Widgets\Reports\RevenueByMonthChart::class)

        {{-- Quarter + Agent side by side --}}
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            @livewire(\App\Filament\Widgets\Reports\RevenueByQuarterChart::class)
            @livewire(\App\Filament\Widgets\Reports\RevenueByAgentChart::class)
        </div>

        {{-- Lead Source ROI table --}}
        @livewire(\App\Filament\Widgets\Reports\LeadSourceRoiWidget::class)

        {{-- Pipeline Velocity table --}}
        @livewire(\App\Filament\Widgets\Reports\PipelineVelocityWidget::class)

        {{-- Agent Performance table --}}
        @livewire(\App\Filament\Widgets\Reports\AgentPerformanceWidget::class)
    </div>
</x-filament-panels::page>
