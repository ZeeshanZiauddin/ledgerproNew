<?php

namespace App\Filament\Resources\PaySupplierResource\Pages;

use App\Filament\Resources\PaySupplierResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPaySupplier extends EditRecord
{
    protected static string $resource = PaySupplierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
