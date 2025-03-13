<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReceiptResource\Pages;
use App\Models\Card;
use App\Models\Receipt;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontFamily;
use Filament\Tables;
use Filament\Tables\Columns\Column;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use NumberFormatter;

class ReceiptResource extends Resource
{
    protected static ?string $model = Receipt::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $navigationGroup = 'Resources';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema(self::getFormSchema())
            ->columns(3);
    }

    public static function getFormSchema(): array
    {
        return [
            Section::make()->schema([
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
                                ->disabled()
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
                    ->relationship('card', 'card_name')
                    ->searchable()
                    ->preload()
                    ->inlineLabel()
                    ->live(debounce: 100)
                    ->afterStateUpdated(function ($state, $set) {

                        if (!$state) {
                            $set('customer_id', null);
                        }
                        $card = Card::find($state);

                        if ($card) {
                            $set('customer_id', $card->customer_id);
                        }
                    }),

                Forms\Components\Select::make('customer_id')
                    ->searchable()
                    ->inlineLabel()
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
            ])->compact()->columns(3),

        ];
    }

    public static function getTableSchema(): array
    {
        return [
            Tables\Columns\TextColumn::make('name')->label('#')->sortable()->searchable(),
            Tables\Columns\TextColumn::make('user.name')->sortable()->searchable(),
            Tables\Columns\TextColumn::make('card.card_name')->sortable(),
            Tables\Columns\TextColumn::make('customer.name')->searchable(),
            Tables\Columns\TextColumn::make('created_at')->label('date')->date('j M y')->sortable(),
            Tables\Columns\TextColumn::make('user.name')->label('issued_by')->searchable(),
            Tables\Columns\TextColumn::make('total')->sortable(),
        ];
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns(self::getTableSchema())
            ->filters([
                SelectFilter::make('user.name') // Create a filter for user
                    ->label('User') // Set the filter label
                    ->options(function () {
                        return \App\Models\User::all()->pluck('name', 'id'); // Fetch all users and use their name and id as options
                    }),
                SelectFilter::make('card_id')
                    ->label('Card')
                    ->options(\App\Models\Card::pluck('card_name', 'id'))
                    ->default(function (Builder $query) {
                        $cardId = request()->query('card_id') ?? null;
                        if ($cardId) {
                            return $cardId;
                        }
                        return null;
                    })
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('viewInvoice')
                    ->label('Invoice')
                    ->icon('heroicon-o-document-text')
                    ->slideOver()
                    ->modalHeading(fn($record) => 'Invoice for Receipt : ' . $record->name)
                    ->modalWidth('3xl')
                    ->modalContent(function ($record) {
                        $data = static::getReceiptDataForPdf($record);
                        return static::loadPdf($data);
                    })
                    ->modalSubmitActionLabel('Email')
                    ->modalSubmitAction(),
                Tables\Actions\ActionGroup::make([

                    Tables\Actions\DeleteAction::make()
                        ->requiresConfirmation()
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('name')
                    ->label('Receipt Name'),
                TextEntry::make('created_at')
                    ->since()
                    ->label('Created At'),
                TextEntry::make('user.name')
                    ->badge()
                    ->color('primary')
                    ->label('Issued by'),
                TextEntry::make('card.card_name')
                    ->copyable()
                    ->fontFamily(FontFamily::Serif)
                    ->label('Card ID'),
                TextEntry::make('Bank_no')->copyable()
                    ->fontFamily(FontFamily::Serif),
                TextEntry::make('bank_date'),
                TextEntry::make('dc_cc'),
                TextEntry::make('recon_acc'),
                TextEntry::make('total_amount')
                    ->label('Total changes')
                    ->html()
                    ->badge()->color('success')
                    ->default(function ($record) {
                        return dollar($record->total);
                    }),
                TextEntry::make('any_changes')
                    ->html()
                    ->badge()->color('success')
                    ->default(function ($record) {
                        return dollar($record->changes);
                    }),
            ])
            ->columns(4);
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
            'index' => Pages\ListReceipts::route('/'),
            'create' => Pages\CreateReceipt::route('/create'),
            'edit' => Pages\EditReceipt::route('/{record}/edit'),

        ];
    }
    public static function generateName(): string
    {
        $latest = Receipt::latest('id')->first(); // Get the latest id
        $latestNumber = $latest ? (int) substr($latest->name, 2) : 0; // Extract the number part and increment it
        $newNumber = str_pad($latestNumber + 1, 7, '0', STR_PAD_LEFT); // Increment and pad the number with leading zeros

        return 'RS' . $newNumber; // Prefix with "QR"
    }

    public static function convertNumberToWords($number)
    {
        $formatter = new NumberFormatter('en', NumberFormatter::SPELLOUT);
        return ucfirst($formatter->format($number)) . ' only';
    }

    public static function getReceiptDataForPdf($record): array
    {

        $total_words = self::convertNumberToWords($record->total);
        return [
            'company' => [
                'name' => setting('site_name'),
                'email' => setting('site_email'),
                'phone' => setting('site_phone'),
                'address' => setting('site_address'),
            ],
            'receipt' => [
                'name' => $record->name,
                'card_name' => optional($record->card)->card_name,
                'date' => Carbon::parse($record->bank_date)->format('d-M-Y'),
                'mode' => $record->type,
                'cheque' => $record->dc_cc,
                'total' => $record->total,
                'totalInWords' => $total_words,
                'charges' => $record->charges,
            ],
            'customer' => $record->customer->name,
            'bank' => $record->bank->name,
            'issued_by' => optional($record->user)->name,
            'currency' => setting('site_currency'),
        ];

    }

    public static function loadPdf(array $data)
    {

        $pdf = Pdf::loadView('pdf.receipt', $data);

        $pdfData = base64_encode($pdf->output());
        $pdfSrc = 'data:application/pdf;base64,' . $pdfData;
        return view('components.pdf-viewer', ['pdfSrc' => $pdfSrc]);
    }
}