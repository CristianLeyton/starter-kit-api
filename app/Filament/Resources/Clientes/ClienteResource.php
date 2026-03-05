<?php

namespace App\Filament\Resources\Clientes;

use App\Filament\Resources\Clientes\Pages\ManageClientes;
use App\Models\Client;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\Action as TableAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Auth;

class ClienteResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::UserCircle;

    protected static ?string $recordTitleAttribute = 'descripcion';

    protected static ?string $modelLabel = 'cliente';
    protected static ?string $pluralModelLabel = 'Clientes';
    protected static bool $hasTitleCaseModelLabel = false;
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('numero')
                    ->label('Cliente número')
                    ->numeric()
                    ->unique(ignoreRecord: true)
                    ->nullable(),
                TextInput::make('descripcion')
                    ->label('Descripción')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('descripcion')
            ->columns([
                TextColumn::make('numero')
                    ->label('Cliente número')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('saldo_total')
                    ->label('Saldo total')
                    ->money('USD', locale: 'es')
                    ->sortable(query: function ($query, string $direction) {
                        return $query->orderByRaw(
                            '(SELECT COALESCE(SUM(CASE WHEN type = ? THEN amount ELSE 0 END), 0) - COALESCE(SUM(CASE WHEN type = ? THEN amount ELSE 0 END), 0) FROM movements WHERE movements.client_id = clients.id) ' . $direction,
                            ['compra', 'pago']
                        );
                    }),
            ])
            ->recordActions([
                EditAction::make()->button()->size('xs')->hiddenLabel()->extraAttributes(['title' => 'Editar']),
                TableAction::make('verCuenta')
                    ->label('Ver cuenta')
                    ->icon(Heroicon::MagnifyingGlass)
                    ->url(fn (Client $record): string => \App\Filament\Resources\Cuenta\CuentaResource::getUrl('index', ['tableFilters' => ['client_id' => ['value' => (string) $record->id]]]))
                    ->button()
                    ->size('xs')
                    ->hiddenLabel()
                    ->extraAttributes(['title' => 'Ver cuenta']),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageClientes::route('/'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->hasRole('admin') || Auth::user()?->hasRole('vendedor');
    }
}
