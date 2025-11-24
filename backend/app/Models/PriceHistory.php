<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PriceHistory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'cryptocurrency_id',
        'price'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'created_at' => 'datetime'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'price_histories';

    /**
     * Get the cryptocurrency.
     */
    public function cryptocurrency(): BelongsTo
    {
        return $this->belongsTo(Cryptocurrency::class);
    }
}
