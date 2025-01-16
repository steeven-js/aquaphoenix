<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Ressource Filament pour gérer les clients
 */
class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $slug = 'shop/customers';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Clients';

    protected static ?string $navigationGroup = 'Livraison';

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 1;

    /**
     * Définit le formulaire de création/édition des clients
     *
     * @param Form $form Le formulaire à configurer
     * @return Form Le formulaire configuré
     */
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('email')
                ->email()
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255),
            Forms\Components\TextInput::make('address')
                ->maxLength(255),
            Forms\Components\FileUpload::make('photo'),
            Forms\Components\TextInput::make('phone1')
                ->tel()
                ->maxLength(255),
            Forms\Components\TextInput::make('phone2')
                ->tel()
                ->maxLength(255),
            Forms\Components\TextInput::make('code')
                ->maxLength(255),
            Forms\Components\TextInput::make('commune')
                ->maxLength(255),
        ]);
    }

    /**
     * Définit la table de liste des clients
     *
     * @param Table $table La table à configurer
     * @return Table La table configurée
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone1')
                    ->searchable(),
                Tables\Columns\TextColumn::make('commune')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Définit les pages disponibles pour cette ressource
     *
     * @return array Les pages configurées
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
