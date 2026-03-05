<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $fillable = [
        'numero',
        'descripcion',
    ];

    protected function casts(): array
    {
        return [
            'numero' => 'integer',
        ];
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(Movement::class);
    }

    public function getSaldoTotalAttribute(): float
    {
        $compras = $this->movements()->where('type', 'compra')->sum('amount');
        $pagos = $this->movements()->where('type', 'pago')->sum('amount');

        return (float) ($compras - $pagos);
    }
}
