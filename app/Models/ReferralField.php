<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class ReferralField extends Model
{
    public const CACHE_KEY = 'crm.referral_fields.active';

    protected $fillable = [
        'pipeline_stage_field_id',
        'is_required',
        'position',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'position' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(static function (): void {
            Cache::forget(self::CACHE_KEY);
        });

        static::deleted(static function (): void {
            Cache::forget(self::CACHE_KEY);
        });
    }

    public function stageField(): BelongsTo
    {
        return $this->belongsTo(PipelineStageField::class, 'pipeline_stage_field_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('position')->orderBy('id');
    }
}
