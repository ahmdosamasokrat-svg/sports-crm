<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class ReferralSetting extends Model
{
    public const CACHE_KEY = 'crm.referral_settings_raw';

    private static ?self $memoized = null;

    protected $fillable = [
        'is_enabled',
        'target_pipeline_stage_id',
        'allow_notes',
        'trigger_stage_field_ids',
        'trigger_values',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'allow_notes' => 'boolean',
            'trigger_stage_field_ids' => 'array',
            'trigger_values' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::saved(static function (): void {
            self::flushCache();
        });

        static::deleted(static function (): void {
            self::flushCache();
        });
    }

    public static function flushCache(): void
    {
        self::$memoized = null;
        Cache::forget(self::CACHE_KEY);
    }

    public static function current(): self
    {
        if (self::$memoized !== null) {
            return self::$memoized;
        }

        $raw = Cache::get(self::CACHE_KEY);
        if (is_array($raw)) {
            $setting = new self();
            $setting->setRawAttributes($raw, true);
            $setting->exists = true;
            self::$memoized = $setting;
            return $setting;
        }

        /** @var self $setting */
        $setting = self::query()->first() ?? self::query()->create([
            'is_enabled' => true,
            'target_pipeline_stage_id' => null,
            'target_lead_status_id' => null,
            'allow_notes' => true,
            'trigger_stage_field_ids' => null,
            'trigger_values' => null,
        ]);

        Cache::put(self::CACHE_KEY, $setting->getAttributes(), 3600);
        self::$memoized = $setting;

        return $setting;
    }

    public function targetStage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'target_pipeline_stage_id');
    }

}
