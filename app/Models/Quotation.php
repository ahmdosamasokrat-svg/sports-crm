<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use App\Security\CrmPermission;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Quotation extends Model
{
    use LogsActivity;

    protected $fillable = [
        'quotation_no',
        'client_name',
        'location',
        'prepared_by',
        'quote_date',
        'system_title',
        'grand_total',
        'payload',
        'created_by',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'quote_date' => 'date',
            'grand_total' => 'decimal:2',
            'payload' => 'array',
        ];
    }

    public function scopeAccessibleTo(Builder $query, User $user): Builder
    {
        if ($user->isSuperAdmin() || $user->hasPermission(CrmPermission::QUOTATIONS_VIEW_ALL) || $user->hasPermission('quotations.view_all')) {
            return $query;
        }

        return $query->where('created_by_user_id', $user->getKey());
    }

    public function isAccessibleTo(User $user): bool
    {
        return self::query()
            ->whereKey($this->getKey())
            ->accessibleTo($user)
            ->exists();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by_user_id'
        );
    }
}
