<x-filament-panels::page>
    @php $stats = $this->getStats(); @endphp

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        @foreach(['leads' => 'Leads', 'deals' => 'Deals', 'tasks' => 'Tasks', 'properties' => 'Properties'] as $key => $label)
        <div class="rounded-xl bg-gray-900 border border-gray-700 p-4 text-center">
            <p class="text-2xl font-bold text-amber-400">{{ number_format($stats[$key]) }}</p>
            <p class="text-sm text-gray-400 mt-1">{{ $label }}</p>
        </div>
        @endforeach
    </div>

    <div class="rounded-xl border border-gray-700 bg-gray-900 p-6">
        <h2 class="text-base font-semibold text-gray-100 mb-2">Export Options</h2>
        <p class="text-sm text-gray-400 mb-4">
            Use the buttons above to export your data. Files are generated on-demand and scoped to your team.
        </p>
        <ul class="text-sm text-gray-400 space-y-1 list-disc list-inside">
            <li><strong class="text-gray-200">Export Leads (Excel)</strong> — All lead records as a styled .xlsx file</li>
            <li><strong class="text-gray-200">Export Deals (Excel)</strong> — All deal records as a styled .xlsx file</li>
            <li><strong class="text-gray-200">Export Full ZIP</strong> — Leads, Deals, Tasks, and Properties as CSV files in a single ZIP archive</li>
        </ul>
    </div>

    <div class="rounded-xl border border-gray-700 bg-gray-900 p-6 mt-4">
        <h2 class="text-base font-semibold text-gray-100 mb-2">Manual Database Backup</h2>
        <p class="text-sm text-gray-400">
            For a full database dump, use your hosting panel's database tools (phpMyAdmin, Forge, etc.) or run
            <code class="bg-gray-800 text-amber-300 px-1 rounded">php artisan backup:run</code> if
            <a href="https://github.com/spatie/laravel-backup" class="text-amber-400 hover:underline" target="_blank">spatie/laravel-backup</a>
            is installed and configured.
        </p>
    </div>
</x-filament-panels::page>
