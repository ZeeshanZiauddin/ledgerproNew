<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\CardPassenger;
use App\Models\Payment;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Filament\Forms;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Icetalker\FilamentTableRepeatableEntry\Infolists\Components\TableRepeatableEntry;



class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
        protected static ?string $navigationGroup = 'Reports';
    

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('supplier_id')
                ->label('Supplier')
                ->relationship('supplier', 'name')
                ->required()
                ->searchable()
                ->preload()
                ->reactive(),

            Forms\Components\TextInput::make('cheque_no')
                ->label('Cheque No'),
            Forms\Components\Select::make('bank_id')
                ->label('Bank')
                ->relationship('bank', 'name')
                ->native(false),
            Forms\Components\TextInput::make('total')
                ->numeric()
                ->prefix('£')
                ->suffix(function ($get) {
                    return $get('payable') ?? '0.00';
                })
                ->label('Total Amount'),
            // Forms\Components\Select::make('cards')
            //     ->label('Select Passengers')
            //     ->relationship('tickets', 'card_id')
            //     ->options(function ($get) {
            //         // Check if supplier_id is set
            //         $supplierId = $get('supplier_id');
            //         if (!$supplierId) {
            //             return [];
            //         }
            //         return CardPassenger::query()
            //             ->whereHas('card.supplier', function ($query) use ($supplierId) {
            //                 $query->where('id', $supplierId);
            //             })
            //             ->get()
            //             ->mapWithKeys(function ($passenger) {
            //                 $card = $passenger->card;
            //                 $user = $card ? \App\Models\User::find($card->user_id) : null;
            //                 if (!$card || !$user) {
            //                     return [$passenger->id => 'Invalid passenger'];
            //                 }

            //                 return [
            //                     $passenger->id => $card->card_name . ' | ' . $passenger->name
            //                         . ' | ' . $passenger->ticket_1 . $passenger->ticket_2
            //                         . ' | ' . $passenger->pnr . ' | ' . $passenger->issue_date
            //                         . ' | Cost: ' . ($passenger->cost + $passenger->tax) . "$ | " . $user->name,
            //                 ];
            //             })
            //             ->toArray();
            //     })
            //     ->columnSpanFull()
            //     ->multiple()
            //     ->required()
            //     ->live(true)
            //     ->afterStateUpdated(function ($state, $set, $get) {
            //         $passengers = CardPassenger::with('card')
            //             ->whereIn('id', $state)
            //             ->whereNotNull('issue_date')
            //             ->get();

            //         $repeator = [];
            //         if (!empty($passengers)) {
            //             foreach ($passengers as $passenger) {
            //                 $card = $passenger->card;
            //                 $owner = \App\Models\User::find($card->user_id);
            //                 $repeator[] = [
            //                     'card_name' => $card->card_name,
            //                     'passenger_name' => $passenger->name,
            //                     'tkt' => $passenger->ticket_1 . $passenger->ticket_2,
            //                     'pnr' => $passenger->pnr,
            //                     'total' => $passenger->cost + $passenger->tax,
            //                     'issue_date' => $passenger->issue_date,
            //                     'user' => $owner->name ?? 'Unknown',
            //                 ];
            //             }
            //         }
            //         $set('passengers', $repeator);
            //     })
            //     ->preload(),

            Forms\Components\Textarea::make('details')
                ->label('Details')->columnSpanFull(),

            Forms\Components\Actions::make([
                Forms\Components\Actions\Action::make('select tickets to pay')
                    ->modalHeading('Select Tickets')
                    ->modalSubheading(
                        function (Forms\Get $get) {
                            $supplierId = $get('supplier_id');
                            $supplier = \App\Models\Supplier::find($supplierId);
                            return 'Select Tickets Issued on Cards by' . $supplier->name;
                        }
                    )
                    ->form(
                        function (Forms\Get $get) {
                            $supplierId = $get('supplier_id'); // Get the supplier_id from the main form
                            return [
                                Forms\Components\Select::make('tickets')
                                    ->multiple()
                                    ->required()
                                    ->options(function () use ($supplierId) {

                                if (!$supplierId) {
                                    return [];
                                }
                                return CardPassenger::query()
                                    ->whereNotNull('issue_date')
                                    ->whereHas('card.supplier', function ($query) use ($supplierId) {
                                        $query->where('id', $supplierId);
                                    })
                                    ->get()
                                    ->mapWithKeys(function ($passenger) {
                                        $card = $passenger->card;
                                        $user = $card ? \App\Models\User::find($card->user_id) : null;
                                        if (!$card || !$user) {
                                            return [$passenger->id => 'Invalid passenger'];
                                        }

                                        return [
                                            $passenger->id => $card->card_name . ' | ' . $card->supplier_id . ' | ' . $passenger->name
                                                . ' | ' . $passenger->ticket_1 . $passenger->ticket_2
                                                . ' | ' . $passenger->pnr . ' | ' . $passenger->issue_date
                                                . ' | Cost: ' . ($passenger->cost + $passenger->tax) . "$ | " . $user->name,
                                        ];
                                    })
                                    ->toArray();
                            })
                            ];
                        }
                    )
                    ->action(function ($data, Forms\Get $get, Forms\Set $set) {
                        $passengers = CardPassenger::with('card')
                            ->whereIn('id', $data['tickets'])
                            ->whereNotNull('issue_date')
                            ->get();
                        $repeator = [];
                        $IDs = [];
                        if (!empty($passengers)) {
                            foreach ($passengers as $passenger) {
                                $card = $passenger->card;
                                $owner = \App\Models\User::find($card->user_id);
                                $IDs[] = $passenger->id;
                                $repeator[] = [
                                    'card_name' => $card->card_name,
                                    'passenger_name' => $passenger->name,
                                    'tkt' => $passenger->ticket_1 . $passenger->ticket_2,
                                    'pnr' => $passenger->pnr,
                                    'total' => $passenger->cost + $passenger->tax,
                                    'issue_date' => $passenger->issue_date,
                                    'user' => $owner->name ?? 'Unknown',
                                ];
                            }
                        }
                        $existingPassengers = $get('passengers') ?? [];
                        $mergedPassengers = array_unique(array_merge($existingPassengers, $repeator), SORT_REGULAR);

                        $set('passengers', $mergedPassengers);
                        $set('passenger_ids', $IDs);
                    })
                    ->tooltip(fn($get) => $get('supplier_id') ? 'Alocate tickets' : 'Select the supplier first')
                    ->disabled(fn($get) => !$get('supplier_id'))
            ])
            ,


            Forms\Components\Hidden::make('payable'),
            Forms\Components\Hidden::make('passenger_ids')
                ->afterStateHydrated(function ($state, $get, $set) {
                    if (!$state) {
                        return;
                    }

                    $passengers = CardPassenger::with('card')
                        ->whereIn('id', $state)
                        ->whereNotNull('issue_date')
                        ->get();
                    $repeator = [];
                    $amount = 0;
                    if (!empty($passengers)) {
                        foreach ($passengers as $passenger) {
                            $card = $passenger->card;
                            $owner = \App\Models\User::find($card->user_id);
                            $amount += ($passenger->cost + $passenger->tax);
                            $repeator[] = [
                                'card_name' => $card->card_name,
                                'passenger_name' => $passenger->name,
                                'tkt' => $passenger->ticket_1 . $passenger->ticket_2,
                                'pnr' => $passenger->pnr,
                                'total' => $passenger->cost + $passenger->tax,
                                'issue_date' => $passenger->issue_date,
                                'user' => $owner->name ?? 'Unknown',
                            ];
                        }
                    }
                    $existingPassengers = $get('passengers') ?? [];
                    $mergedPassengers = array_unique(array_merge($existingPassengers, $repeator), SORT_REGULAR);
                    $set('payable', $amount);
                    $set('passengers', $mergedPassengers);
                })
                ->dehydrated(true),

            TableRepeater::make('passengers')
                ->default([])
                ->headers([
                    Header::make('No')->width('250px')->markAsRequired(),
                    Header::make('passenger_name')->width('400px'),
                    Header::make('Tkt no')->width('200px'),
                    Header::make('PNR')->width('140px'),
                    Header::make('total')->width('140px'),
                    Header::make('Issue Date')->width('140px'),
                    Header::make('IssuedBy')->width('140px'),
                ])
                ->schema([
                    Forms\Components\TextInput::make('card_name')->readOnly(),
                    Forms\Components\TextInput::make('passenger_name')->readOnly(),
                    Forms\Components\TextInput::make('tkt')->readOnly(),
                    Forms\Components\TextInput::make('pnr')->readOnly(),
                    Forms\Components\TextInput::make('total')->readOnly(),
                    Forms\Components\DatePicker::make('issue_date')
                        ->native(false)
                        ->displayFormat('dMy'),
                    Forms\Components\TextInput::make('user')->readOnly(),
                ])
                ->columnSpanFull(),



        ]);

    }


    // public static function infolist(Infolist $infolist): Infolist
    // {
    //     return $infolist
    //         ->schema([
    //             TextEntry::make('id')
    //                 ->label('Card Name'),
    //             TextEntry::make('supplier.name')
    //                 ->label('Supplier'),
    //             TextEntry::make('created_at')
    //                 ->since()
    //                 ->label('date'),
    //             TextEntry::make('supplier.name')
    //                 ->label('Card Name'),

    //             TextEntry::make('bank.name')
    //                 ->label('Bank'),
    //             TextEntry::make('details')
    //                 ->label('Details'),
    //             TextEntry::make('passenger_ids')
    //                 ->visible(false)
    //                 ->label('Details'),
    //             TableRepeatableEntry::make('receipts')
    //                 ->schema([
    //                     TextEntry::make('name'),
    //                 ])
    //                 ->columnSpanFull(),
    //         ])
    //         ->columns(5);
    // }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('supplier.name')->label('Supplier'),
                Tables\Columns\TextColumn::make('cheque_no')->label('Cheque No'),
                Tables\Columns\TextColumn::make('bank.name')->label('Bank'),
                Tables\Columns\TextColumn::make('total')->label('Total'),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modal(),
                Tables\Actions\EditAction::make()
                    ->color('warning'),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
