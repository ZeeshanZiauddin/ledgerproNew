<?php

namespace App\Filament\Resources\CardResource\RelationManagers;

use App\Filament\Resources\ReceiptResource;
use App\Models\Card;
use Carbon\Carbon;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;
use Guava\FilamentModalRelationManagers\Concerns\CanBeEmbeddedInModals;
use Filament\Forms;

class ReceiptsRelationManager extends RelationManager
{
    use CanBeEmbeddedInModals;
    protected static string $relationship = 'receipts';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Fieldset::make('Receipt Details')
                    ->schema(
                        [
                            Forms\Components\TextInput::make('name')
                                ->default(fn() => ReceiptResource::generateName())
                                ->required()
                                ->inlineLabel()
                                ->disabled()
                                ->maxLength(255),
                            Forms\Components\DatePicker::make('created_at')
                                ->label('Date')
                                ->displayFormat('dMY')
                                ->default(now()) // Get date from created_at
                                ->native(false)->inlineLabel(),
                            Forms\Components\TextInput::make('year')
                                ->default(fn($get) => $get('created_at') ? Carbon::parse($get('created_at'))->year : Carbon::now()->year) // Get year from created_at
                                ->disabled()->inlineLabel(),

                            Forms\Components\Select::make('user_id')
                                ->default(fn() => auth()->id())
                                ->native(false)
                                ->relationship(name: 'user', titleAttribute: 'name')->inlineLabel(),
                        ]
                    )
                    ->columns(4)
                    ->columnSpanFull(),

                Forms\Components\Select::make('card_id')
                    ->searchable()
                    ->inlineLabel()
                    ->default(function () {
                        return $this->ownerRecord->id;
                    })
                    ->preload()
                    ->relationship('card', 'card_name'),
                Forms\Components\Select::make('customer_id')
                    ->searchable()
                    ->inlineLabel()
                    ->default(function () {
                        $card = Card::find($this->ownerRecord->id);
                        return $card->customer_id;
                    })
                    ->preload()
                    ->relationship('customer', 'name'),


                Forms\Components\TextInput::make('modified_by')
                    ->inlineLabel()
                    ->maxLength(255),

                Forms\Components\TextInput::make('bank_no')
                    ->inlineLabel()
                    ->maxLength(255),
                Forms\Components\TextInput::make('dc_cc')
                    ->inlineLabel()
                    ->maxLength(255),
                Forms\Components\TextInput::make('total')
                    ->default(0)
                    ->inlineLabel()
                    ->numeric(),

                Forms\Components\TextInput::make('changes')
                    ->default(0)
                    ->inlineLabel()
                    ->numeric(),
                Forms\Components\Select::make('type')
                    ->label('Type')
                    ->reactive()
                    ->native(false)
                    ->inlineLabel()
                    ->default('cash')
                    ->options([
                        'bank' => 'Bank',
                        'cash' => 'Cash',
                    ])
                    ->required()
                    ->placeholder('Payment type'),
                Forms\Components\Select::make('recon_acc')
                    ->relationship('bank', 'name')
                    ->inlineLabel()
                    ->visible(fn($get): bool => $get('type') === 'bank'),
                Forms\Components\DatePicker::make('bank_date')
                    ->native(false)
                    ->displayFormat(' j M y')
                    ->label('Date')
                    ->inlineLabel()
                    ->nullable()
                    ->visible(fn($get): bool => $get('type') === 'bank'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns(ReceiptResource::getTableSchema())
            ->filters([
                //
            ])
            ->headerActions([

                Tables\Actions\CreateAction::make('New')
                    ->icon('heroicon-s-plus')
                    ->modalWidth(MaxWidth::ThreeExtraLarge)
                    ->mutateFormDataUsing(function (array $data): array {
                        // Set the `card_id` to the ID of the related card
                        $data['card_id'] = $this->ownerRecord->id;

                        return $data;
                    }),

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }
}