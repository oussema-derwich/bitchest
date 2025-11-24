<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'crypto_id',
        'price_threshold',
        'type', // 'above' or 'below'
        'is_active'
    ];

    protected $casts = [
        'price_threshold' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function crypto()
    {
        return $this->belongsTo(Cryptocurrency::class);
    }
}