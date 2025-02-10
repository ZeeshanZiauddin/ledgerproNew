<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PayRefundResource\Pages;
use App\Filament\Resources\PayRefundResource\RelationManagers;
use App\Models\PayRefund;
use App\Models\RefundPassenger;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Filament\Forms;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Support\Enums\VerticalAlignment;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PayRefundResource extends Resource
{
    protected static ?string $model = PayRefund::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLable = 'Pay Refunds';
    protected static ?string $navigationGroup = 'Reports';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'default' => 5,

                ])
                    ->schema([
                        Section::make()
                            ->columns([
                                'default' => 2,
                            ])
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Refund Name')
                                    ->inlineLabel()
                                    ->required()
                                    ->maxLength(255)
                                    ->default(fn() => PayRefund::generatePayRefundName())
                                    ->disabled(),
                                Forms\Components\Select::make('issued_by')
                                    ->relationship('issuer', 'name')
                                    ->label('Issued By')
                                    ->default(auth()->user()->id)
                                    ->required()
                                    ->inlineLabel()
                                    ->searchable(),


                                Forms\Components\DatePicker::make('date')
                                    ->label('Refund Date')
                                    ->inlineLabel()
                                    ->required(),

                                Forms\Components\Select::make('modified_by')
                                    ->relationship('modifier', 'name')
                                    ->label('Modified By')
                                    ->inlineLabel()
                                    ->default(auth()->user()->id)
                                    ->nullable()
                                    ->searchable(),

                                Forms\Components\Select::make('customer_id')
                                    ->relationship('customer', 'name')
                                    ->label('Customer')
                                    ->required()
                                    ->inlineLabel()
                                    ->searchable(),

                                Forms\Components\Select::make('bank_id')
                                    ->relationship('bank', 'name')
                                    ->label('Bank')
                                    ->nullable()
                                    ->inlineLabel()
                                    ->searchable(),

                                Forms\Components\TextInput::make('cheque_no')
                                    ->label('Chq No')
                                    ->inlineLabel()
                                    ->nullable(),

                                Forms\Components\TextInput::make('total_amount')
                                    ->label('Total Amount')

                                    ->inlineLabel()
                                    ->required(),
                                Forms\Components\Textarea::make('details')
                                    ->label('Details')
                                    ->columnSpanFull()
                                    ->nullable(),

                            ])->columnSpan(3),
                        Section::make('Refund Details')
                            ->description('Select refunds to allocate for this payment.')
                            ->headerActions([
                                Action::make('Allocate')
                                    ->icon('heroicon-s-receipt-refund')
                                    ->modalHeading('Allocate Refunds')
                                    ->modalDescription('Select refunds to allocate for this payment.')
                                    ->modalWidth('5xl')
                                    ->form(function (Get $get) {
                                        $customer = $get('customer_id');
                                        return [
                                            CheckboxList::make('selected_refunds')
                                                ->label('Available Refunds')
                                                ->options(
                                                    function () use ($customer) {
                                                        return RefundPassenger::with('card')->whereHas('card', function ($query) use ($customer) {
                                                            $query->where('customer_id', $customer ?? null);
                                                        })
                                                            ->whereDoesntHave('payRefunds')
                                                            ->get()->mapWithKeys(function ($refund) {
                                                                return [$refund->id => "{$refund->card->card_name} | {$refund->record_no} | {$refund->ref_to_cus}"];
                                                            });
                                                    }
                                                )
                                                ->descriptions(
                                                    function () use ($customer) {
                                                        return RefundPassenger::with('card')->whereHas('card', function ($query) use ($customer) {
                                                            $query->where('customer_id', $customer ?? null);
                                                        })
                                                            ->whereDoesntHave('payRefunds')
                                                            ->get()->mapWithKeys(function ($refund) {
                                                                $currency = setting('site_currency');
                                                                return [$refund->id => "{$refund->name} | {$currency}{$refund->sale} | {$currency}{$refund->cost} | {$currency}{$refund->margin}"];
                                                            });
                                                    }
                                                )
                                                ->default(function (Get $get, ?PayRefund $record) {
                                                    if ($record) {
                                                        // Get already allocated refund IDs for this PayRefund record
                                                        return $record->refundPassengers()->pluck('refund_passengers.id')->toArray();
                                                    }
                                                    return [];
                                                })

                                                ->bulkToggleable()
                                                ->columns(1)
                                        ];
                                    })
                                    ->action(function (array $data, $record, $set) {

                                        // Get previously allocated refunds
                                        $existingRefunds = $record->refundPassengers()->pluck('refund_passengers.id')->toArray();

                                        // Merge newly selected refunds with existing ones
                                        $allRefunds = array_unique(array_merge($existingRefunds, $data['selected_refunds']));

                                        // Attach all refunds (preserving previous allocations)
                                        $record->refundPassengers()->sync($allRefunds);

                                        // Fetch refund details
                                        $refunds = RefundPassenger::with('card')->whereIn('id', $allRefunds)->get();
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
                                                $record->loadMissing('refundPassengers.card'); // Eager-load relationships
                                                $totalAmount = $record->refundPassengers->sum('ref_to_cus');

                                                $set('passengers', $record->refundPassengers->map(function ($refund) {
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
                                                $record->refundPassengers()->sync($selectedRefunds);
                                                $record->loadMissing('refundPassengers.card'); // Eager-load relationships
                                                $totalAmount = $record->refundPassengers->sum('ref_to_cus');
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
                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('No')->searchable(),
                Tables\Columns\TextColumn::make('date')->label('Date')->sortable(),
                Tables\Columns\TextColumn::make('customer.name')->label('Customer')->searchable(),
                Tables\Columns\TextColumn::make('cheque_no')->label('Cheque Number')->sortable(),
                Tables\Columns\TextColumn::make('total_amount')->prefix(fn() => setting('site_currency'))->label('Total Amount')->sortable(),
                Tables\Columns\TextColumn::make('issuer.name')->label('Issued By')->sortable(),
                Tables\Columns\TextColumn::make('modifier.name')->label('Modified By')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('Created At')->dateTime()->since()->sortable(),
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
            'index' => Pages\ListPayRefunds::route('/'),
            //  'create' => Pages\CreatePayRefund::route('/create'),
            //'edit' => Pages\EditPayRefund::route('/{record}/edit'),
        ];
    }
}