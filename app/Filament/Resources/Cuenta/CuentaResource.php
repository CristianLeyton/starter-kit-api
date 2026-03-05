<?php

namespace App\Filament\Resources\Cuenta;

use App\Filament\Resources\Cuenta\Pages\ManageCuenta;
use App\Models\Client;
use App\Models\Movement;
use App\Models\Payment;
use App\Models\Sale;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\Action as TableAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class CuentaResource extends Resource
{
    protected static ?string $model = Movement::class;

    protected static ?string $slug = 'cuenta';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentText;

    protected static ?string $modelLabel = 'movimiento';
    protected static ?string $pluralModelLabel = 'Cuenta';
    protected static bool $hasTitleCaseModelLabel = false;
    protected static ?int $navigationSort = 5;

    public static function getNavigationLabel(): string
    {
        return Auth::user()?->hasRole('cliente') ? 'Mi cuenta' : 'Cuenta';
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->orderBy('created_at')->orderBy('id');

        if (Auth::user()?->hasRole('cliente') && Auth::user()?->client_id) {
            $query->where('client_id', Auth::user()->client_id);
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Movimiento')
                    ->formatStateUsing(fn ($state) => $state === Movement::TYPE_COMPRA ? 'Compra' : 'Pago')
                    ->badge()
                    ->color(fn ($state) => $state === Movement::TYPE_COMPRA ? 'success' : 'info'),
                TextColumn::make('compra_display')
                    ->label('Compra')
                    ->formatStateUsing(fn (Movement $record) => $record->type === Movement::TYPE_COMPRA ? '$ ' . number_format((float) $record->amount, 0, ',', '.') : '$'),
                TextColumn::make('pago_display')
                    ->label('Pago')
                    ->formatStateUsing(fn (Movement $record) => $record->type === Movement::TYPE_PAGO ? '$ ' . number_format((float) $record->amount, 0, ',', '.') : '$'),
                TextColumn::make('running_balance')
                    ->label('Saldo')
                    ->getStateUsing(function (Movement $record) {
                        $balance = Movement::where('client_id', $record->client_id)
                            ->where(function ($q) use ($record) {
                                $q->where('created_at', '<', $record->created_at)
                                    ->orWhere(fn ($q2) => $q2->where('created_at', $record->created_at)->where('id', '<=', $record->id));
                            })
                            ->orderBy('created_at')
                            ->orderBy('id')
                            ->get()
                            ->reduce(function (float $carry, Movement $m) {
                                return $carry + ($m->type === Movement::TYPE_COMPRA ? (float) $m->amount : -(float) $m->amount);
                            }, 0.0);
                        return $balance;
                    })
                    ->formatStateUsing(fn ($state) => '$ ' . number_format((float) $state, 0, ',', '.')),
            ])
            ->recordActions([
                TableAction::make('detalle')
                    ->label('Detalle')
                    ->icon(Heroicon::MagnifyingGlass)
                    ->button()
                    ->size('xs')
                    ->hiddenLabel()
                    ->extraAttributes(['title' => 'Detalle'])
                    ->modalHeading(fn (Movement $record) => $record->type === Movement::TYPE_COMPRA ? 'Detalle de venta' : 'Detalle de pago')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar')
                    ->form(function (Movement $record) {
                        if ($record->type === Movement::TYPE_COMPRA) {
                            $sale = Sale::with(['items.product', 'user'])->find($record->movementable_id);
                            if (! $sale) {
                                return [\Filament\Forms\Components\TextInput::make('_')->label('')->disabled()->default('Sin datos')];
                            }
                            $itemsText = $sale->items->map(fn ($item) => $item->quantity . ' u. ' . ($item->product?->descripcion) . ' — $' . number_format((float) $item->unit_price, 0, ',', '.') . ' = $' . number_format((float) $item->subtotal, 0, ',', '.'))->implode("\n");
                            return [
                                \Filament\Forms\Components\TextInput::make('_items')->label('Productos')->disabled()->default($itemsText)->columnSpanFull(),
                                \Filament\Forms\Components\TextInput::make('_total')->label('Total')->disabled()->default($sale->total),
                                \Filament\Forms\Components\TextInput::make('_method')->label('Método de pago')->disabled()->default($sale->payment_method === 'contado' ? 'Contado' : 'Transferencia'),
                                \Filament\Forms\Components\TextInput::make('_abonado')->label('Total abonado')->disabled()->default($sale->total_abonado),
                                \Filament\Forms\Components\TextInput::make('_vendedor')->label('Vendedor')->disabled()->default($sale->user?->name . ' ' . ($sale->user?->lastname ?? '')),
                            ];
                        }
                        $payment = Payment::with('user')->find($record->movementable_id);
                        if (! $payment) {
                            return [\Filament\Forms\Components\TextInput::make('_')->label('')->disabled()->default('Sin datos')];
                        }
                        return [
                            \Filament\Forms\Components\TextInput::make('_amount')->label('Monto')->disabled()->default($payment->amount),
                            \Filament\Forms\Components\TextInput::make('_method')->label('Método de pago')->disabled()->default($payment->payment_method === 'contado' ? 'Contado' : 'Transferencia'),
                            \Filament\Forms\Components\TextInput::make('_date')->label('Fecha')->disabled()->default($payment->created_at?->format('d/m/Y')),
                        ];
                    }),
            ])
            ->filters(
                Auth::user()?->hasRole('cliente') ? [] : [
                    SelectFilter::make('client_id')
                        ->label('Cliente')
                        ->relationship('client', 'descripcion')
                        ->searchable()
                        ->preload(),
                ]
            );
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCuenta::route('/'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }
}
