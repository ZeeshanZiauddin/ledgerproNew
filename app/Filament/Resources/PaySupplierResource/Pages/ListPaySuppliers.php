<?php

namespace App\Filament\Resources\PaySupplierResource\Pages;

use App\Filament\Resources\PaySupplierResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPaySuppliers extends ListRecords
{
    protected static string $resource = PaySupplierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
