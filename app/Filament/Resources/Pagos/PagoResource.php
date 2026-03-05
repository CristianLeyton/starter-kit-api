<?php

namespace App\Filament\Resources\Pagos;

use App\Filament\Resources\Pagos\Pages\ManagePagos;
use App\Models\Payment;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PagoResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Banknotes;

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $modelLabel = 'pago';
    protected static ?string $pluralModelLabel = 'Pagos';
    protected static bool $hasTitleCaseModelLabel = false;
    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('client_id')
                    ->label('Cliente')
                    ->relationship('client', 'descripcion')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->descripcion . ' (#' . $record->numero . ')')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('amount')
                    ->label('Monto')
                    ->numeric()
                    ->required()
                    ->minValue(0.01)
                    ->prefix('$'),
                Select::make('payment_method')
                    ->label('Método de pago')
                    ->options([
                        'contado' => 'Efectivo',
                        'transferencia' => 'Transferencia',
                    ])
                    ->default('contado')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('client.descripcion')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Monto')
                    ->money('USD', locale: 'es')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->label('Método')
                    ->formatStateUsing(fn ($state) => $state === 'contado' ? 'Efectivo' : 'Transferencia'),
            ])
            ->recordActions([
                EditAction::make()->button()->size('xs')->hiddenLabel()->extraAttributes(['title' => 'Editar']),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePagos::route('/'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('admin') || auth()->user()?->hasRole('vendedor');
    }
}
