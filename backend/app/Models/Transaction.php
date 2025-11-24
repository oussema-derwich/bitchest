<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'cryptocurrency_id',
        'type',
        'quantity',
        'price_at_transaction',
        'eur_amount',
    ];

    protected $casts = [
        'quantity' => 'decimal:8',
        'price_at_transaction' => 'decimal:2',
        'eur_amount' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cryptocurrency(): BelongsTo
    {
        return $this->belongsTo(Cryptocurrency::class);
    }
}
