<?php

namespace App\Filament\Widgets\Reports;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class ReportFilterWidget extends Widget implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.widgets.reports.report-filter';

    protected int | string | array $columnSpan = 'full';

    protected static bool $isDiscovered = false;

    protected static ?int $sort = 0;

    public string $preset = 'this_month';
    public ?string $date_from = null;
    public ?string $date_until = null;

    public function mount(): void
    {
        $this->preset     = session('report_preset', 'this_month');
        $this->date_from  = session('report_date_from');
        $this->date_until = session('report_date_until');
        $this->applyPreset();
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Select::make('preset')
                ->label('Period')
                ->options([
                    'this_week'   => 'This Week',
                    'this_month'  => 'This Month',
                    'this_quarter'=> 'This Quarter',
                    'this_year'   => 'This Year',
                    'last_month'  => 'Last Month',
                    'last_3_months' => 'Last 3 Months',
                    'custom'      => 'Custom Range',
                ])
                ->default('this_month')
                ->reactive()
                ->afterStateUpdated(fn () => $this->applyPreset()),
            DatePicker::make('date_from')
                ->label('From')
                ->visible(fn () => $this->preset === 'custom'),
            DatePicker::make('date_until')
                ->label('Until')
                ->visible(fn () => $this->preset === 'custom'),
        ]);
    }

    public function applyFilter(): void
    {
        $this->applyPreset();
    }

    private function applyPreset(): void
    {
        $now = Carbon::now();

        [$from, $until] = match ($this->preset) {
            'this_week'    => [$now->startOfWeek()->toDateString(), $now->copy()->endOfWeek()->toDateString()],
            'this_month'   => [$now->copy()->startOfMonth()->toDateString(), $now->copy()->endOfMonth()->toDateString()],
            'this_quarter' => [$now->copy()->startOfQuarter()->toDateString(), $now->copy()->endOfQuarter()->toDateString()],
            'this_year'    => [$now->copy()->startOfYear()->toDateString(), $now->copy()->endOfYear()->toDateString()],
            'last_month'   => [$now->copy()->subMonth()->startOfMonth()->toDateString(), $now->copy()->subMonth()->endOfMonth()->toDateString()],
            'last_3_months'=> [$now->copy()->subMonths(3)->startOfMonth()->toDateString(), $now->copy()->endOfMonth()->toDateString()],
            'custom'       => [$this->date_from, $this->date_until],
            default        => [$now->copy()->startOfMonth()->toDateString(), $now->copy()->endOfMonth()->toDateString()],
        };

        session(['report_preset' => $this->preset, 'report_date_from' => $from, 'report_date_until' => $until]);

        $this->dispatch('report-filter-changed');
    }
}
