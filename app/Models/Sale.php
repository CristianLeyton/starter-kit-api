<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Sale extends Model
{
    protected $fillable = [
        'client_id',
        'user_id',
        'total',
        'payment_method',
        'total_abonado',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'total_abonado' => 'decimal:2',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class, 'sale_id');
    }

    public function movement(): MorphOne
    {
        return $this->morphOne(Movement::class, 'movementable');
    }
}
