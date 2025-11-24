<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cryptocurrency extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'symbol',
        'current_price',
        'logo_path'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'current_price' => 'decimal:2'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cryptos';

    /**
     * Get the wallet cryptos for the cryptocurrency.
     */
    public function walletCryptos(): HasMany
    {
        return $this->hasMany(WalletCrypto::class);
    }

    /**
     * Get the transactions for the cryptocurrency.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the price histories for the cryptocurrency.
     */
    public function priceHistories(): HasMany
    {
        return $this->hasMany(PriceHistory::class);
    }
}
