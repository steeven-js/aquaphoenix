<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $slug = 'shop/orders';

    protected static ?string $recordTitleAttribute = 'number';

    protected static ?string $modelLabel = 'Bon de livraison';

    protected static ?string $navigationGroup = 'Livraison';

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informations Client')
                ->schema([
                    Forms\Components\Select::make('customer_id')
                        ->relationship('customer', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('email')
                                ->email()
                                ->required()
                                ->unique()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('phone1')
                                ->tel()
                                ->maxLength(255),
                        ])
                        ->columnSpan('full'),
                ])->columns(2),

            Forms\Components\Section::make('Détails de la commande')
                ->schema([
                    Forms\Components\TextInput::make('number')
                        ->label('Numéro de commande')
                        ->default(function () {
                            $lastOrder = Order::orderBy('id', 'desc')->first();
                            $lastNumber = $lastOrder ? (int) preg_replace('/[^0-9]/', '', $lastOrder->number) : 0;
                            return 'CMD-' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
                        })
                        ->disabled()
                        ->dehydrated()
                        ->required(),
                    Forms\Components\Select::make('status')
                        ->options([
                            'en progression' => 'En progression',
                            'livré' => 'Livré',
                            'annulé' => 'Annulé',
                        ])
                        ->default('en progression')
                        ->required()
                        ->native(false),
                    Forms\Components\DatePicker::make('published_at')
                        ->label('Date de publication')
                        ->default(now()),
                    Forms\Components\DatePicker::make('delivered_date')
                        ->label('Date de livraison'),
                ])->columns(2),

            Forms\Components\Section::make('Produits')
                ->schema([
                    Forms\Components\Repeater::make('items')
                        ->relationship()
                        ->schema([
                            Forms\Components\Select::make('product_id')
                                ->label('Produit')
                                ->relationship('product', 'name')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->createOptionForm([
                                    Forms\Components\TextInput::make('name')
                                        ->required(),
                                    Forms\Components\Textarea::make('description'),
                                ]),
                            Forms\Components\TextInput::make('qty')
                                ->label('Quantité')
                                ->numeric()
                                ->default(1)
                                ->required()
                                ->minValue(1),
                            Forms\Components\TextInput::make('sort')
                                ->numeric()
                                ->default(0)
                                ->hidden(),
                        ])
                        ->orderColumn('sort')
                        ->defaultItems(1)
                        ->reorderable()
                        ->columnSpanFull()
                        ->columns(2),
                ]),

            Forms\Components\Section::make('Notes')
                ->schema([
                    Forms\Components\Textarea::make('notes')
                        ->label('Notes')
                        ->maxLength(65535)
                        ->columnSpanFull(),
                ])->columns(2),

            // Champs cachés pour usage backend
            Forms\Components\Hidden::make('report_delivered')
                ->default(false),
            Forms\Components\Hidden::make('url'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->label('N° Commande')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Client')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable()
                    ->colors([
                        'warning' => 'en progression',
                        'success' => 'livré',
                        'danger' => 'annulé',
                    ]),
                Tables\Columns\TextColumn::make('published_at')
                    ->label('Publication')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('delivered_date')
                    ->label('Livraison')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'en progression' => 'En progression',
                        'livré' => 'Livré',
                        'annulé' => 'Annulé',
                    ]),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Action::make('delivery_note')
                        ->label('Bon de livraison')
                        ->icon('heroicon-o-document-text')
                        ->url(fn (Order $record) => route('order.delivery-note.download', $record))
                        ->openUrlInNewTab()
                        ->color('success'),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
