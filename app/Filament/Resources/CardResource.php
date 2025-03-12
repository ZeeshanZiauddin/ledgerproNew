<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CardResource\Pages;
use App\Filament\Resources\CardResource\RelationManagers\ReceiptsRelationManager;
use App\Models\Airline;
use App\Models\Card;
use App\Models\CardItinerary;
use App\Models\Receipt;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontFamily;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Table;
use Filament\Forms\Components\Group;
use Guava\FilamentModalRelationManagers\Actions\Table\RelationManagerAction;
use Icetalker\FilamentTableRepeatableEntry\Infolists\Components\TableRepeatableEntry;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Filament\Forms\Components\Tabs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CardResource extends Resource
{
    protected static ?string $model = Card::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Resources';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'card_name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema(components:
                [
                    Section::make()
                        ->schema([


                            Group::make()
                                ->schema([
                                    Forms\Components\TextInput::make('card_name')
                                        ->default(self::generateCardName())
                                        ->disabled() // Disable the field to prevent manual editing
                                        ->inlineLabel()
                                        ->label('Card No.'),
                                    Forms\Components\Hidden::make('id'),
                                    Forms\Components\Hidden::make('current_itinerary'),
                                    Forms\Components\Hidden::make('itinerary_ids'),



                                    Forms\Components\DatePicker::make('created_at')
                                        ->label('Date')
                                        ->displayFormat('d-M-Y')
                                        ->native(false)
                                        ->inlineLabel()
                                        ->default(fn() => Carbon::now())->disabled(),

                                    Forms\Components\Select::make('user_id')
                                        ->inlineLabel()
                                        ->default(fn() => auth()->user()->id)
                                        ->relationship('user', 'name')->disabled(),
                                    Forms\Components\Select::make('airline_id')
                                        ->placeholder('000')
                                        ->inlineLabel()
                                        ->relationship('airline', modifyQueryUsing: fn(Builder $query) => $query->orderBy('code')->orderBy('iata'), )
                                        ->getOptionLabelFromRecordUsing(fn(Model $record) => "{$record->code} {$record->iata}")
                                        ->searchable(['code', 'iata'])
                                        ->native(false)
                                        ->preload()
                                        ->getOptionLabelUsing(function ($value) {
                                            $airline = Airline::find($value);
                                            return $airline ? $airline->iata . ' - ' . $airline->code : 'Unknown';
                                        })
                                        ->afterStateUpdated(function ($state, $set, $get) {
                                            if ($state) {
                                                $passengers = $get('passenger') ?? [];
                                                // Update the 'ticket_1' field for each passenger
                                                $updatedPassengers = array_map(function ($passenger) use ($state) {
                                                    $passenger['ticket_1'] = $state;
                                                    return $passenger;
                                                }, $passengers);

                                                // Set the updated repeater data
                                                $set('passenger', $updatedPassengers);
                                            }
                                        }),
                                ])
                                ->columns(4)
                                ->columnSpanFull(),

                            Group::make()
                                ->schema([

                                    Forms\Components\Select::make('customer_id')
                                        ->relationship('customer', 'name')  // Relationship to the Customer model, assuming it has a `name` field
                                        ->nullable()
                                        ->native(false)
                                        ->inlineLabel()
                                        ->searchable()
                                        ->preload()
                                        ->live(debounce: 300)
                                        ->afterStateUpdated(function ($state, $set) {
                                            if ($state) {
                                                $customer = \App\Models\Customer::find($state);
                                                if ($customer) {
                                                    $set('contact_name', $customer->name);
                                                    $set('contact_email', $customer->email);
                                                    $set('contact_mobile', $customer->phone);
                                                    $set('contact_address', $customer->address);
                                                }
                                            }
                                        })
                                        ->placeholder('Select a Customer'),

                                    Forms\Components\Select::make('supplier_id')
                                        ->relationship('supplier', 'name')  // Relationship to the Supplier model, assuming it has a `name` field
                                        ->nullable()
                                        ->inlineLabel()
                                        ->searchable()
                                        ->preload()
                                        ->native(false)
                                        ->placeholder('Select a Supplier'),

                                    Forms\Components\Select::make('inquiry_id')
                                        ->relationship('inquiry', 'inquiry_name')  // Relationship to the Supplier model, assuming it has a `name` field
                                        ->nullable()
                                        ->preload()
                                        ->inlineLabel()
                                        ->searchable()  // Make the field searchable
                                        ->placeholder(placeholder: 'Inquiry ID'),
                                ])
                                ->columns(1)->columnSpan(1),

                            // Group for contact details
                            Group::make()
                                ->schema([

                                    Forms\Components\TextInput::make('contact_name')
                                        ->nullable()
                                        ->inlineLabel(),

                                    Forms\Components\TextInput::make('contact_email')
                                        ->nullable()
                                        ->inlineLabel(),
                                    Forms\Components\TextInput::make('contact_address')
                                        ->inlineLabel()
                                        ->nullable(),
                                ])->columns(1)->columnSpan(2),
                            Group::make()
                                ->schema([

                                    Forms\Components\TextInput::make('contact_mobile')
                                        ->inlineLabel()->label('Mobile No')->nullable(),
                                    Forms\Components\TextInput::make('contact_home_number')
                                        ->inlineLabel()->label('Home No')->nullable(),
                                    Textarea::make('itinerary')
                                        ->hiddenLabel()
                                        ->placeholder("Paste the itinerary here...")
                                        ->rows(1)
                                        ->hintAction(
                                            Action::make('copy')
                                                ->icon('heroicon-s-clipboard')
                                                ->modalWidth('xl')
                                                ->form(function (Forms\Get $get, $operation) {
                                                    $isCreate = $operation === 'create';
                                                    $itineraries = [];

                                                    if ($isCreate) {
                                                        // For "create" operation: Fetch all itineraries from `itinerary_ids`
                                                        $itineraryIds = $get('itinerary_ids') ?? [];
                                                        $itineraries = CardItinerary::whereIn('id', $itineraryIds)->get()->toArray();
                                                    } else {
                                                        // For "edit" operation: Fetch itineraries by card ID
                                                        $cardId = $get('id');
                                                        if ($cardId) {
                                                            $itineraries = CardItinerary::where('card_id', $cardId)->get()->toArray();
                                                        }
                                                    }

                                                    // Generate the repeater form
                                                    return [
                                                        Forms\Components\Repeater::make('itineraries')
                                                            ->schema([
                                                                Forms\Components\Textarea::make('itinerary')
                                                                    ->rows(4)
                                                                    ->label('Itinerary Details')
                                                                    ->default(fn($record) => $record['itinerary'] ?? null),
                                                            ])
                                                            ->hiddenLabel()
                                                            ->addable(false)
                                                            ->reorderable(false)
                                                            ->deletable(false)
                                                            ->default($itineraries) // Populate repeater with fetched itineraries
                                                            ->label('Itineraries')
                                                            ->columns(1),
                                                    ];
                                                })
                                        )
                                        ->hintAction(
                                            Action::make('paste')
                                                ->icon('heroicon-s-clipboard')
                                                ->action(function ($livewire, $state) {

                                                    $livewire->dispatch('paste-from-clipboard');
                                                })
                                        )
                                        ->extraAttributes([
                                            'x-data' => '{
                                        copyToClipboard(text) {
                                            if (navigator.clipboard && navigator.clipboard.writeText) {
                                                navigator.clipboard.writeText(text).then(() => {
                                                    $tooltip("Copied to clipboard", { timeout: 1500 });
                                                }).catch(() => {
                                                    $tooltip("Failed to copy", { timeout: 1500 });
                                                });
                                            } else {
                                                const textArea = document.createElement("textarea");
                                                textArea.value = text;
                                                textArea.style.position = "fixed";
                                                textArea.style.opacity = "0";
                                                document.body.appendChild(textArea);
                                                textArea.select();
                                                try {
                                                    document.execCommand("copy");
                                                    $tooltip("Copied to clipboard", { timeout: 1500 });
                                                } catch (err) {
                                                    $tooltip("Failed to copy", { timeout: 1500 });
                                                }
                                                document.body.removeChild(textArea);
                                            }
                                        },
                                        pasteFromClipboard() {
                                            if (navigator.clipboard && navigator.clipboard.readText) {
                                                navigator.clipboard.readText().then((text) => {
                                                        this.state = text; 
                                                        $wire.set(\'data.itinerary\', text);
                                                       this.$refs.itinerary.dispatchEvent(new Event(\'input\', { bubbles: true }));
            
                                                }).catch(() => {
                                                    $tooltip("Failed to paste", { timeout: 1500 });
                                                });
                                            } else {
                                                $tooltip("Clipboard API not available", { timeout: 1500 });
                                            }
                                        }
                                    }',
                                            'x-on:copy-to-clipboard.window' => 'copyToClipboard($event.detail.text)',
                                            'x-on:paste-from-clipboard.window' => 'pasteFromClipboard()',
                                            'x-ref' => 'itinerary',
                                            'class' => 'hidden',
                                        ])
                                        ->afterStateUpdated(
                                            function ($state, $set, $get, $operation) {
                                                static::processItinerary($state, $set, $get, $operation);
                                            }
                                        )
                                        ->live(debounce: 200)

                                ]),
                        ])->compact()->columns(4),
                    Tabs::make('Tabs')
                        ->tabs([
                            Tabs\Tab::make('Passengers')
                                ->icon('heroicon-o-user-group')
                                ->schema([
                                    TableRepeater::make('passengers')
                                        ->relationship('passengers')
                                        ->default([])
                                        ->hiddenLabel()
                                        ->extraAttributes(['data-repeater' => 'passengers'])
                                        ->headers([
                                            Header::make('No')->width('80px'),
                                            Header::make('Passenger Name')->width('250px')->markAsRequired(),
                                            Header::make('Ticket')->width('120px'),
                                            Header::make('')->width('180px'),
                                            Header::make('Sale')->width('140px'),
                                            Header::make('Net')->width('140px'),
                                            Header::make('tax')->width('140px'),
                                            Header::make('Issue date')->width('150px'),
                                            Header::make('Profit')->width('140px'),
                                            Header::make('Pnr')->width('150px'),
                                        ])
                                        ->schema([

                                            Forms\Components\TextInput::make('record_no')
                                                ->nullable(),

                                            Forms\Components\TextInput::make('name')
                                                ->nullable(),

                                            Forms\Components\Select::make('ticket_1')
                                                ->placeholder(placeholder: '000')
                                                ->relationship('airline', 'code')
                                                ->nullable()
                                                ->default(function ($get) {
                                                    // dd($get('airline_id'));
                                                    return $get('airline_id');
                                                }),

                                            Forms\Components\TextInput::make('ticket_2')
                                                ->label(false)
                                                ->placeholder('0000000000')
                                                ->maxLength(10)
                                                ->minLength(10)
                                                ->nullable(),

                                            Forms\Components\TextInput::make('sale')
                                                ->nullable()
                                                ->default(0)
                                                ->extraAttributes(['data-field' => 'sale']),
                                            Forms\Components\TextInput::make('cost')
                                                ->nullable()
                                                ->default(0)
                                                ->extraAttributes(['data-field' => 'cost']),
                                            Forms\Components\TextInput::make('tax')
                                                ->nullable()
                                                ->default(0)
                                                ->extraAttributes(['data-field' => 'tax']),
                                            Forms\Components\DatePicker::make('issue_date')
                                                ->native(false)
                                                ->displayFormat('d M y')
                                                ->default(null)
                                                ->placeholder('dd mm yy')
                                                ->nullable(),

                                            Forms\Components\TextInput::make('margin')
                                                ->nullable()
                                                ->live()
                                                ->extraAttributes(['data-field' => 'margin'])
                                                ->default(0),

                                            Forms\Components\TextInput::make('pnr')
                                                ->nullable(),
                                        ])
                                        ->extraItemActions([
                                            Action::make('refund')
                                                ->hidden(fn($operation) => $operation === 'create')
                                                ->icon('heroicon-s-arrow-up-on-square-stack')
                                                ->action(function (array $arguments, Repeater $component, Get $get, Set $set) {
                                                    // Get the existing refunds or initialize an empty array
                                                    $previousRefunds = $get('passenger_refunds') ?? [];

                                                    // Get the current item's raw data
                                                    $itemData = $component->getRawItemState($arguments['item']);

                                                    // Safely create the fields array
                                                    $fields = [
                                                        'record_no' => $itemData['record_no'],
                                                        'card_passenger_id' => $itemData['id'],
                                                        'card_id' => $itemData['card_id'] ?? $get('id'),
                                                        'name' => $itemData['name'] ?? '',
                                                        'sale' => $itemData['sale'] ?? 0.00,
                                                        'cost' => $itemData['cost'] ?? 0.00,
                                                        'tax' => $itemData['tax'] ?? 0.00,
                                                        'ref_to_cus' => $itemData['ref_to_cus'] ?? 0.00,
                                                        'ref_to_vendor' => $itemData['ref_to_vendor'] ?? 0.00,
                                                        'sale_return' => $itemData['sale_return'] ?? 0.00,
                                                        'pur_return' => $itemData['pur_return'] ?? 0.00,
                                                        'apply_date' => $itemData['apply_date'] ?? null,
                                                        'approve_date' => $itemData['approve_date'] ?? null,
                                                        'user_id' => auth()->id(),
                                                    ];

                                                    // Merge the new refund with existing ones, ensuring uniqueness
                                                    $updatedRefunds = collect([...$previousRefunds, $fields])
                                                        ->unique(fn($item) => $item['name'] . '-' . $item['record_no'] . '-' . $item['card_passenger_id'])
                                                        ->values()
                                                        ->toArray();

                                                    // Check if a new refund was added
                                                    if (count($updatedRefunds) > count($previousRefunds)) {
                                                        // Set the updated refunds
                                                        $set('passenger_refunds', $updatedRefunds);

                                                        // Show success notification
                                                        \Filament\Notifications\Notification::make()
                                                            ->title('Ticket added to refund tab')
                                                            ->success()
                                                            ->send();
                                                    } else {
                                                        // Show warning notification
                                                        \Filament\Notifications\Notification::make()
                                                            ->title('Passenger is already in the refund list')
                                                            ->warning()
                                                            ->send();
                                                    }
                                                })
                                                ->iconButton()
                                        ])
                                        ->columnSpanFull(),
                                ]),
                            Tabs\Tab::make('Flight Details')
                                ->icon('heroicon-o-rocket-launch')
                                ->schema([
                                    TableRepeater::make('flights')
                                        ->relationship(name: 'flights')
                                        ->hiddenLabel()
                                        ->headers([
                                            Header::make('airline')->width('250px')->markAsRequired(),
                                            Header::make('flight')->width('120px'),
                                            Header::make('class')->width('180px'),
                                            Header::make('date')->width('150px'),
                                            Header::make('from')->width('150px'),
                                            Header::make('to')->width('140px'),
                                            Header::make('Dep time')->width('140px'),
                                            Header::make('Arr time')->width('140px'),
                                        ])
                                        ->label('Flights')
                                        ->schema([
                                            Forms\Components\TextInput::make('airline')
                                                ->required(),
                                            Forms\Components\TextInput::make('flight')
                                                ->required(),
                                            Forms\Components\TextInput::make('class')
                                                ->required(),
                                            Forms\Components\DatePicker::make('date')
                                                ->displayFormat('dM')
                                                ->native(false)
                                                ->required(),
                                            Forms\Components\TextInput::make('from')
                                                ->required(),
                                            Forms\Components\TextInput::make('to')
                                                ->required(),
                                            Forms\Components\TextInput::make('dep')
                                                ->required(),
                                            Forms\Components\TextInput::make('arr')
                                                ->nullable(),
                                        ])
                                        ->default([])
                                        ->columnSpanFull()
                                        ->createItemButtonLabel('Add Flight'),
                                ]),
                            Tabs\Tab::make('Other Sale')
                                ->icon('heroicon-o-rocket-launch')
                                ->schema([
                                    TableRepeater::make('otherSales')
                                        ->relationship(name: 'otherSales')
                                        ->hiddenLabel()
                                        ->headers([
                                            Header::make('Details')->markAsRequired(),
                                            Header::make('Sale')->width('140px'),
                                            Header::make('Cost')->width('140px'),
                                            Header::make('Issue Date')->width('180px'),
                                            Header::make('Supplier')->width('200px'),
                                        ])
                                        ->label('Flights')
                                        ->schema([
                                            Forms\Components\Hidden::make('card_id')
                                                ->default(function ($get) {
                                                    return $get('id');
                                                }),
                                            Forms\Components\TextInput::make('details')
                                                ->placeholder('Sale details')
                                                ->required(),
                                            Forms\Components\TextInput::make('sale')
                                                ->numeric()
                                                ->placeholder('Sale')

                                                ->default(0.00),
                                            Forms\Components\TextInput::make('cost')
                                                ->numeric()
                                                ->placeholder('Cost')
                                                ->default(0.00),
                                            Forms\Components\DatePicker::make('issue_date')
                                                ->displayFormat('d/M/Y')
                                                ->native(false)
                                                ->placeholder('dd/mm/yy')
                                                ->required(),
                                            Forms\Components\Select::make('supplier_id')
                                                ->relationship('supplier', 'name')
                                                ->searchable()
                                                ->preload(),

                                        ])
                                        ->default([])
                                        ->columnSpanFull()
                                        ->createItemButtonLabel('Add Flight'),
                                ]),
                            Tabs\Tab::make('Refund')
                                ->icon('heroicon-o-rocket-launch')
                                ->schema([
                                    TableRepeater::make('passenger_refunds')
                                        ->relationship('passengerRefunds') // Ensure this relationship exists
                                        ->default([]) // Provide a default empty array
                                        ->hiddenLabel()
                                        ->headers([
                                            Header::make('No')->width('80px'),
                                            Header::make('Passenger Name')->width('250px')->markAsRequired(),
                                            Header::make('Sale')->width('140px'),
                                            Header::make('Cost')->width('140px'),
                                            Header::make('Tax')->width('140px'),
                                            Header::make('Refund Cus Chr')->width('140px'),
                                            Header::make('Refund Ven Chr')->width('140px'),
                                            Header::make('Sale Return')->width('140px'),
                                            Header::make('Pur Return')->width('140px'),
                                            Header::make('ApplyDate')->width('180px'),
                                            Header::make('ApproveDate')->width('180px'),
                                            Header::make('Issued')->width('140px'),
                                        ])
                                        ->schema([
                                            Forms\Components\TextInput::make('record_no')->nullable(),
                                            Forms\Components\Hidden::make('card_passenger_id')->nullable(),
                                            Forms\Components\Hidden::make('card_id')->nullable(),
                                            Forms\Components\TextInput::make('name')->required(),
                                            Forms\Components\TextInput::make('sale')->default(0)->nullable()
                                            ,
                                            Forms\Components\TextInput::make('cost')->default(0)->nullable(),
                                            Forms\Components\TextInput::make('tax')->default(0)->nullable(),
                                            Forms\Components\TextInput::make('ref_to_cus')->nullable()
                                                ->afterStateUpdated(function ($get, $set, $state) {
                                                    $val = $get('sale') - $state;
                                                    $set('sale_return', $val);
                                                })
                                                ->live(onBlur: true),
                                            Forms\Components\TextInput::make('ref_to_vendor')->nullable()
                                                ->afterStateUpdated(function ($get, $set, $state) {
                                                    $val = ($get('cost') + $get('tax')) - $state;
                                                    $set('pur_return', $val);
                                                })
                                                ->live(onBlur: true),
                                            Forms\Components\TextInput::make('sale_return')->default(0)->nullable(),
                                            Forms\Components\TextInput::make('pur_return')->default(0)->nullable(),
                                            Forms\Components\DatePicker::make('apply_date')->nullable(),
                                            Forms\Components\DatePicker::make('approve_date')->nullable(),
                                            Forms\Components\Hidden::make('user_id')->nullable()->default(auth()->user()->id),
                                        ])
                                        ->addable(false)
                                        ->columnSpanFull()

                                ]),

                            Tabs\Tab::make('Remarks')
                                ->icon('heroicon-o-rocket-launch')
                                ->schema([
                                    TableRepeater::make('Remarks')
                                        ->hiddenLabel()
                                        ->relationship('cardRemarks')
                                        ->default([])
                                        ->headers([
                                            Header::make('Remark'),
                                            Header::make('Issued by')->width('180px'),
                                        ])
                                        ->label('Remarks')
                                        ->schema(function () {
                                            return [
                                                Forms\Components\TextInput::make('message')
                                                    ->required()
                                                    ->placeholder('Add Remark ...'),
                                                Forms\Components\Select::make('user_id')
                                                    ->relationship('user', 'name')
                                                    ->default(auth()->user()->id)
                                                    ->required(),
                                            ];
                                        })
                                        ->default([])
                                        ->columnSpanFull()
                                        ->createItemButtonLabel('Add Remark')
                                ]),
                            Tabs\Tab::make('Reminders')
                                ->icon('heroicon-o-rocket-launch')
                                ->schema([
                                    TableRepeater::make('Reminders')
                                        ->hiddenLabel()
                                        ->relationship('cardReminders')
                                        ->default([])
                                        ->headers([
                                            Header::make('Detail'),
                                            Header::make(name: 'date')->width('150px'),
                                            Header::make('For User')->width('180px'),
                                            Header::make('Issued by')->width('180px'),
                                        ])
                                        ->label('Flights')
                                        ->schema(function () {
                                            return [
                                                Forms\Components\TextInput::make('details')->required()
                                                    ->placeholder('Enter Reminder Details'),
                                                Forms\Components\DatePicker::make('reminder_date')
                                                    ->required(),
                                                Forms\Components\Select::make('for_user_id')
                                                    ->relationship('user', 'name')
                                                    ->placeholder('Users...')
                                                    ->searchable()
                                                    ->preload()
                                                    ->required(),
                                                Forms\Components\Hidden::make('created_by')->default(auth()->user()->id),
                                                Forms\Components\Select::make('by_user_id')
                                                    ->placeholder('Users...')
                                                    ->relationship('user', 'name')
                                                    ->default(auth()->user()->id)
                                                    ->required(),
                                            ];
                                        })
                                        ->default([])
                                        ->columnSpanFull()
                                        ->createItemButtonLabel('Add Reminder'),

                                ]),
                        ])
                        ->columnSpanFull(),
                    Grid::make([
                        'default' => 8,
                    ])
                        ->schema([
                            TextInput::make('tkt_sale')
                                ->label('Tkt Sale')
                                ->afterStateHydrated(function (TextInput $component, Get $get, Set $set) {
                                    $passengers = $get('passengers');
                                    if (is_array($passengers)) {
                                        $totalSale = array_sum(array_column($passengers, 'sale'));
                                    } else {
                                        $totalSale = 0;
                                    }
                                    $set('tkt_sale', $totalSale);
                                })
                                ->mask(RawJs::make('$money($input)'))
                                ->stripCharacters(',')
                                ->numeric(),
                            TextInput::make('tkt_cost')
                                ->label('Tkt Cost')
                                ->afterStateHydrated(function (TextInput $component, Get $get, Set $set) {
                                    $passengers = $get('passengers');
                                    if (is_array($passengers)) {
                                        $totalSale = array_sum(array_column($passengers, 'cost'));
                                    } else {
                                        $totalSale = 0;
                                    }
                                    $set('tkt_cost', $totalSale);
                                })
                                ->mask(RawJs::make('$money($input)'))
                                ->stripCharacters(',')
                                ->numeric(),
                            TextInput::make('other_sale')
                                ->label('Other sale')
                                ->afterStateHydrated(function (TextInput $component, Get $get, Set $set) {
                                    $Other = $get('otherSales');
                                    if (is_array($Other)) {
                                        $totalSale = array_sum(array_column($Other, 'sale'));
                                    } else {
                                        $totalSale = 0;
                                    }
                                    $set('other_sale', $totalSale);
                                }),
                            TextInput::make('other_cost')
                                ->label('Other Cost')
                                ->afterStateHydrated(function (TextInput $component, Get $get, Set $set) {
                                    $Other = $get('otherSales');
                                    if (is_array($Other)) {
                                        $totalSale = array_sum(array_column($Other, column_key: 'cost'));
                                    } else {
                                        $totalSale = 0;
                                    }
                                    $set('other_cost', $totalSale);
                                }),
                            TextInput::make('sale_return')
                                ->label('Sale Return')
                                ->afterStateHydrated(function (TextInput $component, Get $get, Set $set) {
                                    $refund = $get('passenger_refunds');
                                    if (is_array($refund)) {
                                        $sale = array_sum(array_column($refund, 'sale_return'));
                                    } else {
                                        $sale = 0;
                                    }
                                    $set('sale_return', $sale);
                                }),
                            TextInput::make('pur_return')
                                ->label('Pur Return')
                                ->afterStateHydrated(function (TextInput $component, Get $get, Set $set) {
                                    $refund = $get('passenger_refunds');
                                    if (is_array($refund)) {
                                        $pur = array_sum(array_column($refund, 'pur_return'));
                                    } else {
                                        $pur = 0;
                                    }
                                    $set('pur_return', $pur);
                                }),
                            TextInput::make('total_receipt')
                                ->label('Total Receipt')
                                ->afterStateHydrated(function (Get $get, Set $set) {
                                    $id = $get('id');
                                    $receipts = Receipt::where('card_id', $id)->get()->toArray();
                                    if (is_array($receipts)) {
                                        $total = array_sum(array_column($receipts, 'total'));
                                    } else {
                                        $total = 0;
                                    }
                                    $set('total_receipt', $total);
                                }),
                            TextInput::make(name: 'refund_paid')
                                ->label('Refund Paid')
                                ->afterStateHydrated(function (Get $get, Set $set) {

                                }),


                            TextInput::make('sales_price')
                                ->label('Sales')

                                ->extraAttributes(['data-field' => 'total_sale'])
                                ->default(0),


                            TextInput::make('net_cost')
                                ->label('Cost')
                                ->extraAttributes(['data-field' => 'total_cost'])
                                ->default(0),
                            TextInput::make('tax')
                                ->extraAttributes(['data-field' => 'total_tax'])
                                ->default(0),
                            TextInput::make('margin')
                                ->extraAttributes(['data-field' => 'total_margin'])
                                ->default(0),
                            Forms\Components\TextInput::make('total_paid')
                                ->label('Paid')
                                ->readOnly()
                                ->default(0)
                                ->afterStateHydrated(function ($set, $record) {
                                    if ($record) {
                                        $set('total_paid', $record->receipts()->sum('total'));
                                    }
                                }),
                        ])
                        ->columns(11),
                ])
            ->columns(4);
    }
    public function mount(): void
    {
        parent::mount();
        $this->dispatchBrowserEvent('form-loaded');
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('card_name')->sortable()->searchable(),

                Tables\Columns\TextColumn::make('sales_price')->sortable(),
                Tables\Columns\TextColumn::make('margin')->sortable(),
                Tables\Columns\BadgeColumn::make('Payment')
                    ->sortable()
                    ->html()
                    ->getStateUsing(function ($record) {
                        $res = $record->getReceiptsStatus();
                        return [
                            'lable' => $res['status']['lable'],
                        ];
                    })
                    ->color(
                        function ($record) {
                            $res = $record->getReceiptsStatus('status');
                            return $res['color'];
                        }
                    ),

                Tables\Columns\TextColumn::make('created_at')->since()->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->slideOver(),
                Tables\Actions\EditAction::make()
                    ->color('warning'),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('viewInvoice')
                        ->label('Invoice')
                        ->icon('heroicon-o-document-text')
                        ->slideOver()
                        ->modalHeading(fn($record) => 'Invoice for Card : ' . $record->card_name)
                        ->modalWidth('3xl')
                        ->modalContent(function ($record) {
                            $data = [
                                'company' => [
                                    'name' => setting('site_name'),
                                    'email' => setting('site_email'),
                                    'phone' => setting('site_phone'),
                                    'address' => setting('site_address'),
                                ],
                                'card' => [
                                    'name' => $record->card_name,
                                    'date' => now()->format('d/M/y'),
                                    'due_date' => now()->addDays(30)->format('d/M/y'),
                                    'customer' => $record->customer,
                                    'receipts' => $record->receipts,
                                    'total' => $record->sales_price,
                                    'airline' => $record->airline,
                                ],
                                'issued_by' => $record->user->name,
                                'currency' => setting('site_currency'),
                                'passengers' => $record->passengers,
                                'flights' => $record->flights
                            ];
                            $pdf = Pdf::loadView('pdf.invoice', $data);
                            $pdfData = base64_encode($pdf->output());
                            $pdfSrc = 'data:application/pdf;base64,' . $pdfData;
                            return view('components.pdf-viewer', ['pdfSrc' => $pdfSrc]);

                        })
                        ->modalSubmitActionLabel('Email')
                        ->modalSubmitAction(),
                    \Parallax\FilamentComments\Tables\Actions\CommentsAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
            ])
            ->filters([

                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('user')
                    ->searchable()
                    ->preload(),
                Tables\Filters\QueryBuilder::make()
                    ->constraints([
                        DateConstraint::make('created_at')

                    ])
            ])
            ->bulkActions([
                DeleteBulkAction::make()
            ]);
    }
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\TextEntry::make('card_name')
                    ->label('Card Name'),
                Infolists\Components\TextEntry::make('created_at')
                    ->since()
                    ->label('Date'),
                Infolists\Components\TextEntry::make('user.name')
                    ->label('Issued by'),
                Infolists\Components\TextEntry::make('inquiry.name')
                    ->label('Inquiry No.'),
                Infolists\Components\TextEntry::make('contact_name'),
                Infolists\Components\TextEntry::make('contact_email')->copyable(),
                Infolists\Components\TextEntry::make('contact_mobile')->copyable(),
                Infolists\Components\TextEntry::make('contact_address')->copyable(),
                Infolists\Components\TextEntry::make('sales_price'),
                Infolists\Components\TextEntry::make('net_cost'),
                Infolists\Components\TextEntry::make('tax'),
                Infolists\Components\TextEntry::make('payment_status')
                    ->badge()
                    ->label('Payment Status')
                    ->html()
                    ->getStateUsing(function ($record) {
                        $res = $record->getReceiptsStatus();
                        return [
                            'total' => setting("site_currency") . $res['total'],
                            'lable' => $res['status']['lable'],
                        ];
                    })
                    ->color(
                        function ($record): string {
                            $res = $record->getReceiptsStatus('status');
                            return $res['color'] ?? 'primary';
                        }
                    ),

                TableRepeatableEntry::make('passengers')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->label('Passenger name'),
                        Infolists\Components\TextEntry::make('ticket')
                            ->label('Ticket No.')
                            ->fontFamily(FontFamily::Serif)
                            ->default(
                                function ($record) {
                                    return $record->ticket_1 . ' ' . $record->ticket_2;
                                }
                            )
                            ->copyable(),
                        Infolists\Components\TextEntry::make('issue_date')
                            ->date()
                            ->label('Issue date'),
                        Infolists\Components\TextEntry::make('option_date')
                            ->date()
                            ->label('Option Date'),
                        Infolists\Components\TextEntry::make('pnr')
                            ->label('PNR')
                            ->copyable()
                            ->fontFamily(FontFamily::Mono)

                    ])
                    ->columnSpanFull(),
                TableRepeatableEntry::make('receipts')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->fontFamily(FontFamily::Serif)
                            ->label('#'),
                        Infolists\Components\TextEntry::make('customer.name')
                            ->default('N/A')
                            ->label('Customer'),
                        Infolists\Components\TextEntry::make('bank_no')
                            ->default('Not Entered')
                            ->label('Bank')
                            ->fontFamily(FontFamily::Serif),
                        Infolists\Components\TextEntry::make(name: 'totals')
                            ->badge()
                            ->label('Amount')
                            ->html()
                            ->default(
                                function ($record) {
                                    return setting('site_currency') . $record->total;
                                }
                            )
                            ->color('success'),
                        Infolists\Components\TextEntry::make('user.name')->label('issued_by'),
                        Infolists\Components\TextEntry::make('created_at')->label('Created At')
                            ->date(),
                        Infolists\Components\TextEntry::make('title')
                            ->suffixAction(\Guava\FilamentModalRelationManagers\Actions\Infolist\RelationManagerAction::make()
                                ->label('View Receipts')
                                ->relationManager(ReceiptsRelationManager::make()))

                    ])
                    ->columnSpanFull(),
            ])
            ->columns(4);
    }
    public static function getRelations(): array
    {
        return [
            ReceiptsRelationManager::class,
        ];
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCards::route('/'),
            'create' => Pages\CreateCard::route('/create'),
            'edit' => Pages\EditCard::route('/{record}/edit'),
        ];
    }
    public static function generateCardName(): string
    {
        $latestInquiry = Card::latest('id')->first(); // Get the latest inquiry
        $latestNumber = $latestInquiry ? (int) substr($latestInquiry->card_name, 2) : 0; // Extract the number part and increment it
        $newNumber = str_pad($latestNumber + 1, 7, '0', STR_PAD_LEFT); // Increment and pad the number with leading zeros

        return 'QT' . $newNumber; // Prefix with "QR"
    }

    public static function processItinerary($state, Set $set, Get $get, $operation)
    {
        // Parse the itinerary into passengers and flights
        $lines = array_filter(array_map('trim', explode("\n", $state))); // Remove empty lines and trim
        $pnr = self::extractPNR($lines);
        $airline = $get('airline_id');

        $newPassengers = self::parsePassengers($lines, $pnr);
        $newFlights = self::parseFlights($lines);

        // Set airline ID if flights exist
        if ($newFlights) {
            $airline = Airline::where('iata', $newFlights[0]['airline'])->value('id');
            if ($airline) {
                $set('airline_id', $airline);
            }
        }

        if (self::isValidItinerary($newPassengers, $newFlights)) {
            $existingPassengers = $get('passengers') ?? [];
            $existingFlights = $get('flights') ?? [];

            // Merge passengers and flights
            $mergedPassengers = self::mergeEntities($existingPassengers, $newPassengers, ['name', 'pnr']);
            $mergedFlights = self::mergeEntities($existingFlights, $newFlights, ['airline', 'flight', 'date']);


            $last_itinerary = $get('current_itinerary');

            if ($last_itinerary != $state) {
                // Save itinerary
                $itinerary = CardItinerary::create([
                    'itinerary' => trim($state),
                    'card_id' => $get('id') ?? null,
                ]);

                if ($itinerary) {

                    $itineraryIds = json_decode($get('itinerary_ids') ?? '[]', true);
                    $itineraryIds[] = $itinerary->id;

                    // Update state
                    $set('itinerary_ids', json_encode($itineraryIds));

                    $set('current_itinerary', $state);
                }
            }


            self::assignPassengerNumbers($mergedPassengers, $get);
            $set('passengers', $mergedPassengers);
            $set('flights', $mergedFlights);

        }
    }

    // Extract PNR from itinerary lines
    private static function extractPNR($lines)
    {
        foreach ($lines as $line) {
            if (preg_match('/^[A-Z0-9]{6}(?=\/|$)/', $line, $matches)) {
                return $matches[0];
            }
        }
        return null;
    }

    // Parse passengers from itinerary lines
    private static function parsePassengers($lines, $pnr)
    {
        $passengers = [];
        foreach ($lines as $line) {
            if (preg_match_all('/\d+\.\d+([A-Z\/ ]+ [A-Z]{2,4})/', $line, $matches)) {
                foreach ($matches[1] as $name) {
                    $passengers[] = [
                        'record_no' => 0,
                        'name' => trim($name),
                        'ticket_2' => null,
                        'issue_date' => null,
                        'sale' => 0.00,
                        'cost' => 0.00,
                        'tax' => 0.00,
                        'margin' => 0.00,
                        'pnr' => $pnr,
                    ];
                }
            }
        }
        return $passengers;
    }

    // Parse flights from itinerary lines
    private static function parseFlights($lines)
    {
        $flights = [];
        foreach ($lines as $line) {
            // Match flight details with flexible status code
            if (preg_match('/([A-Z]{2})\s+(\d+)\s+([A-Z])\s+(\d{2}[A-Z]{3})\s+([A-Z]{3})([A-Z]{3})\s+([A-Z]+\d+)\s+(\d{4})\s+(\d{4})/', $line, $matches)) {
                $dateRaw = $matches[4] . date('Y'); // Combine date and year
                $date = Carbon::createFromFormat('dMY', $dateRaw)->format('Y-m-d'); // Format the date

                $flights[] = [
                    'airline' => $matches[1],            // Airline code
                    'flight' => $matches[2],             // Flight number
                    'class' => $matches[3],              // Booking class
                    'date' => $date,                     // Flight date (formatted)
                    'from' => $matches[5],               // Departure airport code
                    'to' => $matches[6],                 // Arrival airport code
                    'status' => $matches[7],             // Flight status (e.g., HK3, TK2, etc.)
                    'dep' => substr($matches[8], 0, 2) . ':' . substr($matches[8], 2, 2), // Departure time (HH:MM)
                    'arr' => substr($matches[9], 0, 2) . ':' . substr($matches[9], 2, 2), // Arrival time (HH:MM)
                ];
            }
        }

        return $flights;
    }


    // Merge new entities with existing ones based on unique keys
    private static function mergeEntities($existing, $new, $uniqueKeys)
    {
        $existingKeys = array_map(fn($item) => implode('|', array_intersect_key($item, array_flip($uniqueKeys))), $existing);
        foreach ($new as $item) {
            $key = implode('|', array_intersect_key($item, array_flip($uniqueKeys)));
            if (!in_array($key, $existingKeys)) {
                $existing[] = $item;
            }
        }
        return $existing;
    }

    // Validate itinerary data
    private static function isValidItinerary($passengers, $flights)
    {
        return !empty($passengers) && !empty($flights);
    }

    // Assign passenger numbers
    private static function assignPassengerNumbers(&$passengers, $get)
    {
        $recordNo = 1; // Initialize the record number variable
        foreach ($passengers as &$passenger) {
            $passenger['record_no'] = $recordNo; // Assign the record number
            $passenger['ticket_1'] = $get('airline_id') ?? null;
            $recordNo++; // Increment the record number
        }
    }



}