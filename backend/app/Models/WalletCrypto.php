<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WalletCrypto extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'wallet_id',
        'cryptocurrency_id',
        'quantity',
        'avg_buy_price'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'decimal:8',
        'avg_buy_price' => 'decimal:2'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wallet_cryptos';

    /**
     * Get the wallet that owns the wallet crypto.
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    /**
     * Get the cryptocurrency.
     */
    public function cryptocurrency(): BelongsTo
    {
        return $this->belongsTo(Cryptocurrency::class);
    }

    /**
     * Get the current value of this holding.
     */
    public function getCurrentValue()
    {
        return $this->quantity * $this->cryptocurrency->current_price;
    }

    /**
     * Get the profit/loss of this holding.
     */
    public function getProfitLoss()
    {
        $currentValue = $this->getCurrentValue();
        $investedValue = $this->quantity * $this->avg_buy_price;
        return $currentValue - $investedValue;
    }

    /**
     * Get the profit/loss percentage.
     */
    public function getProfitLossPercentage()
    {
        $investedValue = $this->quantity * $this->avg_buy_price;
        if ($investedValue == 0) {
            return 0;
        }
        return (($this->getProfitLoss() / $investedValue) * 100);
    }
}
