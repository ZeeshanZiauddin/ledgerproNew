<?php
namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget\Card;
use Filament\Widgets\Widget;

class ResourceStatsWidget extends Widget
{
    protected static string $view = 'filament.widgets.resource-stats-widget';
    protected int|string|array $columnSpan = 'full';

    protected array $resources = [];
    protected int|string $count = 1;

    public function __construct()
    {
        $this->resources = $this->getRegisteredResources();
        $this->count = count($this->resources) <= 3 ? count($this->resources) : 3;
    }

    /**
     * Register new resources here.
     */
    private function getRegisteredResources(): array
    {
        return [
            [
                'resource' => \App\Filament\Resources\ReceiptResource::class,
                'description' => 'This is the receipt resource',
                'color' => '#4ade80',
                'theme' => 'success',
            ],
            [
                'resource' => \App\Filament\Resources\InquiryResource::class,
                'description' => 'This is the Inquirey resource',
                'color' => 'rgb(96, 165, 250)',
                'theme' => 'primary',
            ],
            [
                'resource' => \App\Filament\Resources\InquiryResource::class,
                'description' => 'This is the Inquirey resource',
                'color' => 'rgb(96, 165, 250)',
                'theme' => 'primary',
            ],
            [
                'resource' => \App\Filament\Resources\InquiryResource::class,
                'description' => 'This is the Inquirey resource',
                'color' => 'rgb(96, 165, 250)',
                'theme' => 'primary',
            ]
        ];
    }


    public function getResources(): array
    {
        return $this->resources;
    }
}