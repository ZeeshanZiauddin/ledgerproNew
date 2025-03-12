<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaySupplierResource\Pages;
use App\Filament\Resources\PaySupplierResource\RelationManagers;
use App\Models\CardPassenger;
use App\Models\PaySupplier;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaySupplierResource extends Resource
{
    protected static ?string $model = PaySupplier::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Reports';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->columns([
                        'default' => 3,
                    ])
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Voucher No')
                            ->inlineLabel()
                            ->disabled()
                            ->default(fn() => PaySupplier::generatePayRefundName())
                            ->required(),
                        Forms\Components\Select::make('issued_by')
                            ->label('Issued By')
                            ->inlineLabel()
                            ->default(auth()->user()->id)
                            ->relationship('issuedBy', 'name')
                            ->disabled()
                            ->required(),
                        Forms\Components\DatePicker::make('date')
                            ->label('Date')
                            ->extraInputAttributes(['tabindex' => 1])
                            ->inlineLabel()
                            ->required(),
                        Forms\Components\Select::make('supplier_id')
                            ->label('Supplier')
                            ->inlineLabel()
                            ->relationship('supplier', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('cheque_no')
                            ->label('Chq No')
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('ref_no')
                            ->label('Ref No')
                            ->inlineLabel(),
                        Forms\Components\Select::make('bank_id')
                            ->label('Paid Account')
                            ->inlineLabel()
                            ->relationship('bank', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('total')
                            ->label('Amount')
                            ->inlineLabel()
                            ->default(0)
                            ->required(),

                        Forms\Components\Textarea::make('details')
                            ->label('Details')
                            ->rows(1)
                            ->columnSpanFull(),
                    ]),
                Section::make('Refund Details')
                    ->description('Select refunds to allocate for this payment.')
                    ->headerActions([
                        Action::make('Allocate')
                            ->icon('heroicon-s-receipt-refund')
                            ->modalHeading('Allocate Refunds')
                            ->modalDescription('Select refunds to allocate for this payment.')
                            ->modalWidth('5xl')
                            ->form(function (Get $get) {
                                $supplier = $get('supplier_id');
                                return [
                                    CheckboxList::make('selected_tickets')
                                        ->label('Available Tickets')
                                        ->options(
                                            function () use ($supplier) {
                                                return CardPassenger::with('card')->whereHas('card', function ($query) use ($supplier) {
                                                    $query->where('supplier_id', $supplier ?? null);
                                                })
                                                    ->whereDoesntHave('paySupplier')
                                                    ->get()->mapWithKeys(function ($refund) {
                                                        return [$refund->id => "{$refund->card->card_name} | {$refund->record_no} | {$refund->ref_to_cus}"];
                                                    });
                                            }
                                        )
                                        ->descriptions(
                                            function () use ($supplier) {
                                                return CardPassenger::with('card')->whereHas('card', function ($query) use ($supplier) {
                                                    $query->where('supplier_id', $supplier ?? null);
                                                })
                                                    ->whereDoesntHave('paySupplier')
                                                    ->get()->mapWithKeys(function ($refund) {
                                                        $currency = setting('site_currency');
                                                        return [$refund->id => "{$refund->name} | {$currency}{$refund->sale} | {$currency}{$refund->cost} | {$currency}{$refund->margin}"];
                                                    });
                                            }
                                        )
                                        ->default(function (Get $get, ?PaySupplier $record) {
                                            if ($record) {
                                                // Get already allocated refund IDs for this PayRefund record
                                                return $record->cardPassengers()->pluck('card_passengers.id')->toArray();
                                            }
                                            return [];
                                        })

                                        ->bulkToggleable()
                                        ->columns(1)
                                ];
                            })
                            ->action(function (array $data, $record, $set) {

                                // Get previously allocated refunds
                                $existingRefunds = $record->cardPassengers()->pluck('card_passengers.id')->toArray();
                                // Merge newly selected refunds with existing ones
                                $allRefunds = array_unique(array_merge($existingRefunds, $data['selected_tickets']));

                                // Attach all refunds (preserving previous allocations)
                                $record->cardPassengers()->sync($allRefunds);

                                // Fetch refund details
                                $refunds = CardPassenger::with('card')->whereIn('id', $allRefunds)->get();
                                $totalAmount = $refunds->sum('ref_to_cus');
                                // Map refunds to the format needed for TableRepeater
                                $passengers = $refunds->map(function ($refund) {
                                    return [
                                        'id' => $refund->id,
                                        'card_name' => optional($refund->card)->card_name,
                                        'record_no' => $refund->record_no,
                                        'customer_char' => $refund->ref_to_cus,
                                    ];
                                })->toArray();

                                // Set the passengers in TableRepeater
                                $set('passengers', $passengers);
                                $set('total_amount', $totalAmount);

                                // Show notification
                                Notification::make()
                                    ->title('Refunds Allocated Successfully!')
                                    ->success()
                                    ->send();
                            })
                            ->badge()
                            ->requiresConfirmation()

                    ])
                    ->columns([
                        'default' => 2,
                    ])
                    ->schema(
                        [
                            TableRepeater::make('passengers')
                                ->default([])
                                ->afterStateHydrated(function ($state, $record, $set) {
                                    if ($record) { // Ensure we are editing, not creating
                                        $record->loadMissing('cardPassengers.card'); // Eager-load relationships
                                        $totalAmount = $record->cardPassengers->sum('ref_to_cus');

                                        $set('passengers', $record->cardPassengers->map(function ($refund) {
                                            return [
                                                'id' => $refund->id,
                                                'card_name' => optional($refund->card)->card_name,
                                                'record_no' => $refund->record_no,
                                                'customer_char' => $refund->ref_to_cus,
                                            ];
                                        })->toArray());
                                        $set('total_amount', $totalAmount);
                                    }
                                })
                                ->afterStateUpdated(function ($state, $record, $set) {
                                    if ($record) {
                                        // Get current refund IDs in the repeater
                                        $selectedRefunds = collect($state)->pluck('id')->toArray();

                                        // Remove any refunds that were deselected
                                        $record->cardPassengers()->sync($selectedRefunds);
                                        $record->loadMissing('cardPassengers.card'); // Eager-load relationships
                                        $totalAmount = $record->cardPassengers->sum('ref_to_cus');
                                        $set('total_amount', $totalAmount);
                                    }
                                })
                                ->hiddenLabel()
                                ->headers([
                                    Header::make('ID')->width('150px'),
                                    Header::make('RecNo')->width('80px'),
                                    Header::make('Refund')->markAsRequired(),
                                ])
                                ->schema([
                                    Forms\Components\Hidden::make('id'),

                                    Forms\Components\TextInput::make('card_name')
                                        ->nullable(),
                                    Forms\Components\TextInput::make('record_no')
                                        ->nullable(),
                                    Forms\Components\TextInput::make('customer_char')
                                        ->nullable(),
                                ])
                                ->addable(false)
                                ->columnSpanFull()
                        ]
                    )->columnSpan(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('cheque_no')->sortable(),
                Tables\Columns\TextColumn::make('ref_no')->sortable(),
                Tables\Columns\TextColumn::make('bank.name')->sortable(),
                Tables\Columns\TextColumn::make('supplier.name')->sortable(),
                Tables\Columns\TextColumn::make('total')->sortable(),
                Tables\Columns\TextColumn::make('issuedBy.name')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaySuppliers::route('/'),
            'create' => Pages\CreatePaySupplier::route('/create'),
            'edit' => Pages\EditPaySupplier::route('/{record}/edit'),
        ];
    }
}