<?php

namespace App\Filament\Pages;

use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\RefundPassenger;
use Illuminate\Http\Request;

class RefundReport extends Page implements HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Refund Reports';
    protected static ?string $navigationGroup = 'Reports';
    protected static string $view = 'filament.pages.refund-report';
    public $date_range = 'last_month';
    public $status = 'all';
    public $supplier;
    public $customer;
    public $start_date;
    public $end_date;

    public function mount()
    {
        [$this->start_date, $this->end_date] = $this->getDateRange();
    }


    public function form(Form $form): Form
    {
        return $form->schema([
            Select::make('date_range')
                ->label('Date Range')
                ->options($this->getDateRangeOptions())
                ->default('last_month')
                ->inlineLabel()
                ->required(),

            Select::make('supplier')
                ->label('Supplier')
                ->searchable()
                ->preload()
                ->inlineLabel()
                ->options(fn() => \App\Models\Supplier::pluck('name', 'id')),

            Select::make('customer')
                ->label('Customer')
                ->inlineLabel()
                ->searchable()

                ->preload()
                ->options(fn() => \App\Models\Customer::pluck('name', 'id')),

            Select::make('status')
                ->label('Status')
                ->options([
                    'pending' => 'Pending',
                    'all' => 'All',
                    'approved' => 'Approved',
                ])
                ->inlineLabel()
                ->default('all')
                ->required(),

            Forms\Components\Actions::make([
                Forms\Components\Actions\Action::make('apply_filters')
                    ->label('Load Records')
                    ->action('applyFilters')
                    ->button()
                    ->color('primary'),
            ]),
        ]);
    }


    public function applyFilters()
    {
        [$this->start_date, $this->end_date] = $this->getDateRange();
        $this->dispatch('filtersUpdated', $this->start_date, $this->end_date, $this->status, $this->supplier, $this->customer);
    }
    private function getDateRange(): array
    {
        $ranges = [
            'today' => [now()->startOfDay(), now()->endOfDay()],
            'last_day' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
            'last_3_days' => [now()->subDays(3)->startOfDay(), now()->endOfDay()],
            'last_month' => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            'last_3_months' => [now()->subMonths(3)->startOfMonth(), now()->endOfMonth()],
            'last_6_months' => [now()->subMonths(6)->startOfMonth(), now()->endOfMonth()],
            'last_year' => [now()->subYear()->startOfYear(), now()->endOfYear()],
        ];
        return $ranges[$this->date_range] ?? $ranges['today'];
    }


    private function getDateRangeOptions(): array
    {
        return [
            'today' => 'Today',
            'last_day' => 'Last Day',
            'last_3_days' => 'Last 3 Days',
            'last_month' => 'Last Month',
            'last_3_months' => 'Last 3 Months',
            'last_6_months' => 'Last 6 Months',
            'last_year' => 'Last Year',
        ];
    }


    public function getFilteredData()
    {
        [$start_date, $end_date] = $this->getDateRange();
        $query = RefundPassenger::with('card');

        if ($this->status === 'pending') {
            $query->whereNotNull('apply_date')->whereNull('approve_date');
        } elseif ($this->status === 'approved') {
            $query->whereNotNull('apply_date')->whereNotNull('approve_date');
        }

        if ($this->supplier) {
            $query->whereHas('card', fn($q) => $q->where('supplier_id', $this->supplier));
        }

        if ($this->customer) {
            $query->whereHas('card', fn($q) => $q->where('customer_id', $this->customer));
        }

        return $query->get();
    }

    public function generatePdf(Request $request)
    {
        $this->date_range = $request->input('date_range', 'today');
        $this->status = $request->input('status', 'all');
        $this->supplier = $request->input('supplier');
        $this->customer = $request->input('customer');

        $refunds = $this->getFilteredData();

        $pdf = Pdf::loadView('filament.pages.reports.refunds-report', compact('refunds'))
            ->setOption('font', 'Serif')
            ->setPaper('a4', 'portrait');

        return $pdf->stream('Refund-Report-' . Carbon::now()->format('YmdHis') . '.pdf');
    }

}