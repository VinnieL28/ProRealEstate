<x-filament-widgets::widget>
    <x-filament::section>
        <div style="display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap;">
            <div style="flex:1; min-width:200px;">
                <div style="display:flex; align-items:center; gap:.5rem; margin-bottom:.5rem;">
                    <x-filament::icon icon="heroicon-o-rocket-launch" style="width:1.25rem; height:1.25rem; color:#f59e0b;" />
                    <span style="font-weight:600; color:#f59e0b;">Complete Your Setup</span>
                    <span style="font-size:.85rem; color:#94a3b8;">{{ $percent }}% done</span>
                </div>
                <div style="background:#334155; border-radius:9999px; height:8px; overflow:hidden;">
                    <div style="background:#f59e0b; height:100%; width:{{ $percent }}%; transition:width .4s ease; border-radius:9999px;"></div>
                </div>
            </div>
            <a href="{{ \Filament\Facades\Filament::getUrl() }}/onboarding"
               style="background:#f59e0b; color:#0f172a; padding:.5rem 1.25rem; border-radius:6px; font-weight:600; font-size:.875rem; text-decoration:none; white-space:nowrap;">
                Continue Setup →
            </a>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
