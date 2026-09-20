<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Guardian extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'secondary_phone',
        'email',
        'relationship',
        'notes',
        'created_by_user_id',
    ];

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'guardian_id');
    }

    public function players(): HasMany
    {
        return $this->hasMany(Lead::class, 'guardian_id')
            ->with(['status.stage.category']);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
