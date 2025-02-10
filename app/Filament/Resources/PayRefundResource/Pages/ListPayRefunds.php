<?php

namespace App\Filament\Resources\PayRefundResource\Pages;

use App\Filament\Resources\PayRefundResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPayRefunds extends ListRecords
{
    protected static string $resource = PayRefundResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
