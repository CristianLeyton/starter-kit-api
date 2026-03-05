<?php

namespace App\Filament\Resources\Productos;

use App\Filament\Resources\Productos\Pages\ManageProductos;
use App\Models\Product;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductoResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Cube;

    protected static ?string $recordTitleAttribute = 'descripcion';

    protected static ?string $modelLabel = 'producto';
    protected static ?string $pluralModelLabel = 'Gestión de Productos';
    protected static bool $hasTitleCaseModelLabel = false;
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('descripcion')
                    ->label('Descripción')
                    ->required()
                    ->maxLength(255),
                TextInput::make('precio')
                    ->label('Precio')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->required(),
                TextInput::make('stock_actual')
                    ->label('Stock actual')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('descripcion')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha de alta')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('stock_actual')
                    ->label('Stock actual')
                    ->sortable(),
                TextColumn::make('precio')
                    ->label('Precio')
                    ->money('USD', locale: 'es')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make()->button()->size('xs')->hiddenLabel()->extraAttributes(['title' => 'Editar']),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageProductos::route('/'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('admin') || auth()->user()?->hasRole('vendedor');
    }
}
