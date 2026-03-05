<?php

namespace App\Filament\Resources\Ventas;

use App\Filament\Resources\Ventas\Pages\ManageVentas;
use App\Models\Sale;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VentaResource extends Resource
{
    protected static ?string $model = Sale::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ShoppingCart;

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $modelLabel = 'venta';
    protected static ?string $pluralModelLabel = 'Ventas';
    protected static bool $hasTitleCaseModelLabel = false;
    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('client_id')
                    ->label('Cliente')
                    ->relationship('client', 'descripcion')
                    ->required(),
                TextInput::make('total')
                    ->label('Total')
                    ->numeric()
                    ->disabled(),
                Select::make('payment_method')
                    ->label('Método de pago')
                    ->options([
                        'contado' => 'Efectivo',
                        'transferencia' => 'Transferencia',
                    ]),
                TextInput::make('total_abonado')
                    ->label('Total abonado')
                    ->numeric()
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('client.descripcion')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('total')
                    ->label('Total')
                    ->money('USD', locale: 'es')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Vendedor')
                    ->formatStateUsing(fn ($record) => $record->user?->name . ' ' . ($record->user?->lastname ?? '')),
                TextColumn::make('payment_method')
                    ->label('Método de pago')
                    ->formatStateUsing(fn ($state) => $state === 'contado' ? 'Efectivo' : 'Transferencia'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Detalle')
                    ->modalHeading(fn (Sale $record) => 'Detalle de Venta - ' . $record->created_at->format('d/m/Y') . ' - ' . $record->client?->descripcion)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar')
                    ->mutateRecordDataUsing(function (array $data): array {
                        $sale = Sale::with(['items.product', 'user'])->find($data['id'] ?? null);
                        if ($sale) {
                            $data['_items_text'] = $sale->items->map(fn ($item) => $item->quantity . ' u. ' . ($item->product?->descripcion) . ' — $' . number_format((float) $item->unit_price, 0, ',', '.') . ' = $' . number_format((float) $item->subtotal, 0, ',', '.'))->implode("\n");
                            $data['_vendedor'] = $sale->user?->name . ($sale->user?->lastname ? ' ' . $sale->user->lastname : '');
                            $data['_payment_label'] = $sale->payment_method === 'contado' ? 'Contado' : 'Transferencia';
                        }
                        return $data;
                    })
                    ->form([
                        \Filament\Forms\Components\TextInput::make('_items_text')
                            ->label('Productos')
                            ->disabled()
                            ->columnSpanFull()
                            ->extraInputAttributes(['style' => 'min-height: 120px;']),
                        \Filament\Forms\Components\TextInput::make('total')
                            ->label('Total')
                            ->disabled(),
                        \Filament\Forms\Components\TextInput::make('_payment_label')
                            ->label('Método de pago')
                            ->disabled()
                            ->dehydrated(false),
                        \Filament\Forms\Components\TextInput::make('total_abonado')
                            ->label('Total abonado')
                            ->disabled(),
                        \Filament\Forms\Components\TextInput::make('_vendedor')
                            ->label('Vendedor')
                            ->disabled()
                            ->dehydrated(false),
                    ]),
                EditAction::make()->button()->size('xs')->hiddenLabel()->extraAttributes(['title' => 'Editar']),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageVentas::route('/'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('admin') || auth()->user()?->hasRole('vendedor');
    }
}
