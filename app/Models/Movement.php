<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Movement extends Model
{
    public const TYPE_COMPRA = 'compra';
    public const TYPE_PAGO = 'pago';

    protected $fillable = [
        'client_id',
        'type',
        'amount',
        'movementable_id',
        'movementable_type',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function movementable(): MorphTo
    {
        return $this->morphTo();
    }
}
