<?php

namespace App\Filament\Resources\Ventas\Pages;

use App\Filament\Resources\Ventas\VentaResource;
use App\Models\Client;
use App\Models\Movement;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\DB;

class ManageVentas extends ManageRecords
{
    protected static string $resource = VentaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Venta')
                ->modalHeading('Venta')
                ->form($this->getVentaFormSchema())
                ->using(function (array $data): Sale {
                    return DB::transaction(function () use ($data) {
                        $total = collect($data['items'] ?? [])->sum(fn ($i) => (int) ($i['quantity'] ?? 0) * (float) ($i['unit_price'] ?? 0));
                        $sale = Sale::create([
                            'client_id' => $data['client_id'],
                            'user_id' => auth()->id(),
                            'total' => $total,
                            'payment_method' => $data['payment_method'],
                            'total_abonado' => (float) ($data['total_abonado'] ?? 0),
                        ]);

                        foreach ($data['items'] as $item) {
                            $product = Product::find($item['product_id']);
                            if (! $product) {
                                continue;
                            }
                            $qty = (int) ($item['quantity'] ?? 1);
                            $unitPrice = (float) ($item['unit_price'] ?? 0);
                            $subtotal = $qty * $unitPrice;
                            SaleItem::create([
                                'sale_id' => $sale->id,
                                'product_id' => $product->id,
                                'quantity' => $qty,
                                'unit_price' => $unitPrice,
                                'subtotal' => $subtotal,
                            ]);
                            $product->decrement('stock_actual', $qty);
                        }

                        Movement::create([
                            'client_id' => $sale->client_id,
                            'type' => Movement::TYPE_COMPRA,
                            'amount' => $sale->total,
                            'movementable_id' => $sale->id,
                            'movementable_type' => Sale::class,
                        ]);

                        $totalAbonado = (float) ($data['total_abonado'] ?? 0);
                        if ($totalAbonado > 0) {
                            $payment = Payment::create([
                                'client_id' => $sale->client_id,
                                'user_id' => auth()->id(),
                                'amount' => $totalAbonado,
                                'payment_method' => $data['payment_method'],
                            ]);
                            Movement::create([
                                'client_id' => $payment->client_id,
                                'type' => Movement::TYPE_PAGO,
                                'amount' => $payment->amount,
                                'movementable_id' => $payment->id,
                                'movementable_type' => Payment::class,
                            ]);
                        }

                        return $sale;
                    });
                })
                ->createAnother(false),
        ];
    }

    protected function getVentaFormSchema(): array
    {
        return [
            Select::make('client_id')
                ->label('Cliente')
                ->options(Client::query()->get()->mapWithKeys(fn ($c) => [$c->id => $c->descripcion . ' (#' . $c->numero . ')'])->all())
                ->searchable()
                ->preload()
                ->required()
                ->live()
                ->afterStateUpdated(fn ($state, callable $set) => $set('_client_saldo', $state ? Client::find($state)?->saldo_total : null)),

            TextInput::make('_client_saldo')
                ->label('Estado de cuenta (saldo actual)')
                ->disabled()
                ->dehydrated(false)
                ->prefix('$')
                ->formatStateUsing(fn ($state) => $state !== null ? number_format((float) $state, 0, ',', '.') : '—'),

            Repeater::make('items')
                ->label('Productos')
                ->schema([
                    Select::make('product_id')
                        ->label('Producto')
                        ->options(Product::query()->pluck('descripcion', 'id'))
                        ->required()
                        ->searchable()
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set) {
                            if ($state) {
                                $p = Product::find($state);
                                $set('unit_price', $p?->precio ?? 0);
                            }
                        }),
                    TextInput::make('quantity')
                        ->label('Cantidad')
                        ->numeric()
                        ->default(1)
                        ->minValue(1)
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn ($state, $get, callable $set) => $set('subtotal', (float) ($get('unit_price') ?? 0) * (int) ($state ?? 0))),
                    TextInput::make('unit_price')
                        ->label('Precio unitario')
                        ->numeric()
                        ->minValue(0)
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn ($state, $get, callable $set) => $set('subtotal', (float) ($state ?? 0) * (int) ($get('quantity') ?? 0))),
                    TextInput::make('subtotal')
                        ->label('Subtotal')
                        ->numeric()
                        ->disabled()
                        ->dehydrated(false)
                        ->default(0),
                ])
                ->defaultItems(1)
                ->columns(4)
                ->live()
                ->afterStateUpdated(function ($state, callable $set) {
                    $sum = collect($state ?? [])->sum(fn ($i) => (int) ($i['quantity'] ?? 0) * (float) ($i['unit_price'] ?? 0));
                    $set('total', $sum);
                })
                ->required(),

            TextInput::make('total')
                ->label('Total')
                ->numeric()
                ->disabled()
                ->dehydrated(false)
                ->default(0),

            Select::make('payment_method')
                ->label('Método de pago')
                ->options([
                    'contado' => 'Efectivo',
                    'transferencia' => 'Transferencia',
                ])
                ->default('contado')
                ->required(),

            TextInput::make('total_abonado')
                ->label('Abona')
                ->numeric()
                ->minValue(0)
                ->default(0)
                ->suffix('$'),
        ];
    }
}
