<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RegistrationRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email',
        'role',
        'is_approved',
        'is_rejected',
        'rejection_reason',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'is_rejected' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relation: Une demande d'inscription appartient à un utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
