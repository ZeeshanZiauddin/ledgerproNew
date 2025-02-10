<?php

namespace App\Filament\Resources\PayRefundResource\Pages;

use App\Filament\Resources\PayRefundResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPayRefund extends EditRecord
{
    protected static string $resource = PayRefundResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
