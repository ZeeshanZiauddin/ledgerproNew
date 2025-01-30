<?php

namespace App\Filament\Pages;

use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\RefundPassenger;

class RefundReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Refund Reports';
    protected static ?string $navigationGroup = 'Reports';

    protected static string $view = 'filament.pages.refund-report';

    public $start_date;
    public $end_date;
    public $status;

    public function mount()
    {
        $this->start_date = now()->subMonth()->format('Y-m-d');
        $this->end_date = now()->format('Y-m-d');
        $this->status = 'all';
    }

    public function getFilteredData()
    {
        return RefundPassenger::with('card')->get();
    }

    public function generatePdf()
    {
        $refunds = $this->getFilteredData();
        // dd($refunds);

        $pdf = Pdf::loadView('filament.pages.reports.refunds-report', compact('refunds'))
            ->setOption('font', 'Serif')
            ->setPaper('a4', 'portrait');

        return $pdf->stream('ticket-sale-details-' . Carbon::now()->format('YmdHis') . '.pdf');

    }
}