<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AppointmentSetting extends Model
{
    public const CACHE_KEY = 'crm.appointment_settings.attributes';

    private static ?self $memoized = null;

    protected $fillable = [
        'is_enabled',
        'stage_ids',
        'date_field_key',
        'time_field_key',
        'coach_field_key',
        'status_field_key',
        'allow_quick_actions',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'stage_ids' => 'array',
            'allow_quick_actions' => 'boolean',
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
            'stage_ids' => [17],
            'date_field_key' => 'trial_date',
            'time_field_key' => 'trial_time',
            'coach_field_key' => 'coach',
            'status_field_key' => 'trial_status',
            'allow_quick_actions' => true,
        ]);

        Cache::put(self::CACHE_KEY, $setting->getAttributes(), 3600);
        self::$memoized = $setting;

        return $setting;
    }

    /**
     * Get the mapped pipeline stages.
     *
     * @return Collection<int, PipelineStage>
     */
    public function getMappedStages(): Collection
    {
        $ids = is_array($this->stage_ids) ? array_map('intval', $this->stage_ids) : [];

        if (empty($ids)) {
            return new Collection();
        }

        return PipelineStage::query()
            ->with('category')
            ->whereIn('id', $ids)
            ->get();
    }

    /**
     * Check if a specific stage ID is mapped to appointments.
     */
    public function isStageMapped(int $stageId): bool
    {
        $ids = is_array($this->stage_ids) ? array_map('intval', $this->stage_ids) : [];

        return in_array($stageId, $ids, true);
    }
}
