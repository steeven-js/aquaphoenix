<?php

namespace App\Filament\Resources\Shop;

use App\Filament\Resources\Shop\OrderResource\Pages;
use App\Filament\Resources\Shop\OrderResource\RelationManagers;
use App\Filament\Resources\Shop\OrderResource\Widgets\OrderStats;
use App\Forms\Components\AddressForm;
use App\Models\Shop\Order;
use App\Models\Shop\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;
use Squire\Models\Currency;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $slug = 'shop/orders';

    protected static ?string $recordTitleAttribute = 'number';

    protected static ?string $modelLabel = 'Livraison';

    protected static ?string $navigationGroup = 'Shop';

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema(static::getFormSchema())
                            ->columns(2),

                        Forms\Components\Section::make('Order items')
                            ->schema(static::getFormSchema('items')),
                    ])
                    ->columnSpan(['lg' => fn (?Order $record) => $record === null ? 3 : 2]),

                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Placeholder::make('created_at')
                            ->label('Created at')
                            ->content(fn (Order $record): ?string => $record->created_at?->diffForHumans()),

                        Forms\Components\Placeholder::make('updated_at')
                            ->label('Last modified at')
                            ->content(fn (Order $record): ?string => $record->updated_at?->diffForHumans()),
                    ])
                    ->columnSpan(['lg' => 1])
                    ->hidden(fn (?Order $record) => $record === null),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'en progression' => 'warning',
                        'livré' => 'success',
                        'annulé' => 'danger',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Order Date')
                    ->date()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('url')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('report_delivered')
                    ->label('Rapport envoyé')
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('report_delivered_date')
                    ->label('Rapport envoyé le')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])->defaultSort('number', 'desc')
            ->filters([
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->placeholder(fn ($state): string => 'Dec 18, ' . now()->subYear()->format('Y')),
                        Forms\Components\DatePicker::make('created_until')
                            ->placeholder(fn ($state): string => now()->format('M d, Y')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators['created_from'] = 'Order from ' . Carbon::parse($data['created_from'])->toFormattedDateString();
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Order until ' . Carbon::parse($data['created_until'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('Envoyer mail')
                        ->icon('heroicon-o-envelope')
                        ->url(fn (Order $record): string => route('livraison.mail', $record)),
                    Tables\Actions\Action::make('Imprimer')
                        ->icon('heroicon-o-document-arrow-up')
                        ->url(fn (Order $record): string => route('order.print', $record)),
                ]),
            ])
            ->groupedBulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->action(function () {
                        Notification::make()
                            ->title('Now, now, don\'t be cheeky, leave some records for others to play with!')
                            ->warning()
                            ->send();
                    }),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->groups([
                Tables\Grouping\Group::make('created_at')
                    ->label('Order Date')
                    ->date()
                    ->collapsible(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getWidgets(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScope(SoftDeletingScope::class);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['number', 'customer.name'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        /** @var Order $record */

        return [
            'Customer' => optional($record->customer)->name,
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['customer', 'items']);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::$model::where('status', 'livré')->count();
    }

    public static function getFormSchema(string $section = null): array
    {
        if ($section === 'items') {
            return [
                Forms\Components\Repeater::make('items')
                    ->relationship()
                    ->schema([
                        Forms\Components\Select::make('product_id')
                            ->label('Product')
                            ->options(Product::query()->pluck('name', 'id'))
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('unit_price', Product::find($state)?->price ?? 0))
                            ->columnSpan([
                                'md' => 5,
                            ])
                            ->searchable(),

                        Forms\Components\TextInput::make('qty')
                            ->label('Quantity')
                            ->numeric()
                            ->default(1)
                            ->columnSpan([
                                'md' => 2,
                            ])
                            ->required(),

                    ])
                    ->orderable()
                    ->defaultItems(1)
                    ->disableLabel()
                    ->columns([
                        'md' => 10,
                    ])
                    ->required(),
            ];
        }

        return [

            Forms\Components\Select::make('customer_id')
                ->relationship('customer', 'name')
                ->searchable()
                ->required()
                ->createOptionForm([
                    Forms\Components\TextInput::make('name')
                        ->required(),

                    Forms\Components\TextInput::make('email')
                        ->label('Email address')
                        ->required()
                        ->email()
                        ->unique(),

                    Forms\Components\TextInput::make('phone'),

                ])
                ->createOptionAction(function (Forms\Components\Actions\Action $action) {
                    return $action
                        ->modalHeading('Create customer')
                        ->modalButton('Create customer')
                        ->modalWidth('lg');
                }),

            Forms\Components\DatePicker::make('published_at')
                ->label('Bon de livraison créé le')
                ->displayFormat('d/m/Y')
                ->default(now())
                ->required(),

            Forms\Components\DatePicker::make('delivered_date')
                ->label('Livraison prévue le')
                ->displayFormat('d/m/Y')
                ->default(now())
                ->required(),

            Forms\Components\Select::make('status')
                ->options([
                    'en progression' => 'en progression',
                    'livré' => 'livré',
                    'annulé' => 'annulé',
                ])
                ->required()
                ->native(false),

            Forms\Components\MarkdownEditor::make('notes')
                ->columnSpan('full'),

        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
