<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Movement;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoFrigorificaSeeder extends Seeder
{
    public function run(): void
    {
        $clients = [
            ['numero' => 1, 'descripcion' => 'Doña Sandra'],
            ['numero' => 2, 'descripcion' => 'Pepe Grillo'],
            ['numero' => 3, 'descripcion' => 'Dionisio Salvador de los Andes'],
            ['numero' => 4, 'descripcion' => 'Goku definitivo'],
        ];

        foreach ($clients as $data) {
            Client::firstOrCreate(
                ['numero' => $data['numero']],
                ['descripcion' => $data['descripcion']]
            );
        }

        $products = [
            ['descripcion' => '5kg Hamburguesa Paty', 'precio' => 7000, 'stock_actual' => 100],
            ['descripcion' => '10kg Chorizo Criollo', 'precio' => 11000, 'stock_actual' => 80],
            ['descripcion' => 'Caja de Salchichas 36u', 'precio' => 30000, 'stock_actual' => 50],
            ['descripcion' => 'Kilos de merca', 'precio' => 11000, 'stock_actual' => 200],
        ];

        foreach ($products as $data) {
            Product::firstOrCreate(
                ['descripcion' => $data['descripcion']],
                ['precio' => $data['precio'], 'stock_actual' => $data['stock_actual']]
            );
        }

        $vendedor = User::firstOrCreate(
            ['username' => 'vendedor'],
            [
                'name' => 'Jose',
                'lastname' => 'Jose',
                'email' => 'vendedor@mail.com',
                'password' => bcrypt('vendedor'),
            ]
        );
        if (! $vendedor->hasRole('vendedor')) {
            $vendedor->assignRole('vendedor');
        }

        $clientDoñaSandra = Client::where('numero', 1)->first();
        $clienteUser = User::firstOrCreate(
            ['username' => 'cliente'],
            [
                'name' => 'Sandra',
                'lastname' => 'Cliente',
                'email' => 'cliente@mail.com',
                'password' => bcrypt('cliente'),
                'client_id' => $clientDoñaSandra?->id,
            ]
        );
        if ($clientDoñaSandra && ! $clienteUser->client_id) {
            $clienteUser->update(['client_id' => $clientDoñaSandra->id]);
        }
        if (! $clienteUser->hasRole('cliente')) {
            $clienteUser->assignRole('cliente');
        }

        if (Sale::count() > 0) {
            return;
        }

        $productos = Product::all()->keyBy('descripcion');
        $sale1 = Sale::create([
            'client_id' => $clientDoñaSandra->id,
            'user_id' => $vendedor->id,
            'total' => 120000,
            'payment_method' => 'contado',
            'total_abonado' => 0,
        ]);
        SaleItem::create([
            'sale_id' => $sale1->id,
            'product_id' => $productos['5kg Hamburguesa Paty']->id,
            'quantity' => 3,
            'unit_price' => 7000,
            'subtotal' => 21000,
        ]);
        SaleItem::create([
            'sale_id' => $sale1->id,
            'product_id' => $productos['10kg Chorizo Criollo']->id,
            'quantity' => 1,
            'unit_price' => 11000,
            'subtotal' => 11000,
        ]);
        Movement::create([
            'client_id' => $sale1->client_id,
            'type' => Movement::TYPE_COMPRA,
            'amount' => $sale1->total,
            'movementable_id' => $sale1->id,
            'movementable_type' => Sale::class,
        ]);

        $pago1 = Payment::create([
            'client_id' => $clientDoñaSandra->id,
            'user_id' => $vendedor->id,
            'amount' => 40000,
            'payment_method' => 'transferencia',
        ]);
        Movement::create([
            'client_id' => $pago1->client_id,
            'type' => Movement::TYPE_PAGO,
            'amount' => $pago1->amount,
            'movementable_id' => $pago1->id,
            'movementable_type' => Payment::class,
        ]);

        $sale2 = Sale::create([
            'client_id' => $clientDoñaSandra->id,
            'user_id' => $vendedor->id,
            'total' => 136000,
            'payment_method' => 'contado',
            'total_abonado' => 0,
        ]);
        SaleItem::create([
            'sale_id' => $sale2->id,
            'product_id' => $productos['5kg Hamburguesa Paty']->id,
            'quantity' => 3,
            'unit_price' => 7000,
            'subtotal' => 21000,
        ]);
        SaleItem::create([
            'sale_id' => $sale2->id,
            'product_id' => $productos['10kg Chorizo Criollo']->id,
            'quantity' => 1,
            'unit_price' => 11000,
            'subtotal' => 11000,
        ]);
        SaleItem::create([
            'sale_id' => $sale2->id,
            'product_id' => $productos['Caja de Salchichas 36u']->id,
            'quantity' => 2,
            'unit_price' => 30000,
            'subtotal' => 60000,
        ]);
        SaleItem::create([
            'sale_id' => $sale2->id,
            'product_id' => $productos['Kilos de merca']->id,
            'quantity' => 4,
            'unit_price' => 11000,
            'subtotal' => 44000,
        ]);
        Movement::create([
            'client_id' => $sale2->client_id,
            'type' => Movement::TYPE_COMPRA,
            'amount' => $sale2->total,
            'movementable_id' => $sale2->id,
            'movementable_type' => Sale::class,
        ]);

        $pago2 = Payment::create([
            'client_id' => $clientDoñaSandra->id,
            'user_id' => $vendedor->id,
            'amount' => 100000,
            'payment_method' => 'contado',
        ]);
        Movement::create([
            'client_id' => $pago2->client_id,
            'type' => Movement::TYPE_PAGO,
            'amount' => $pago2->amount,
            'movementable_id' => $pago2->id,
            'movementable_type' => Payment::class,
        ]);
    }
}
