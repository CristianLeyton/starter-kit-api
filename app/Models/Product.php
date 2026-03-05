<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'descripcion',
        'precio',
        'stock_actual',
    ];

    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
            'stock_actual' => 'integer',
        ];
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class, 'product_id');
    }
}
