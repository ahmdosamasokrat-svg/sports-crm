<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\FollowupCustomerField;
use App\Models\PipelineStageField;
use Illuminate\Support\Facades\Schema;

class BirthdayModuleGuard
{
    private static ?bool $cachedEnabled = null;

    /**
     * Determine if the Birthday Module is active and eligible.
     * The module is available ONLY WHEN:
     * 1. An age or birthday field is active in the lead's core data (FollowupCustomerField), OR
     * 2. An age or birthday field is active in any specific stage data (PipelineStageField).
     */
    public static function isEnabled(): bool
    {
        if (self::$cachedEnabled !== null) {
            return self::$cachedEnabled;
        }

        try {
            self::$cachedEnabled = self::hasActiveCoreBirthdayField() || self::hasActiveStageBirthdayField();
        } catch (\Throwable) {
            self::$cachedEnabled = false;
        }

        return self::$cachedEnabled;
    }

    /**
     * Check if core customer fields contain an active birthday or age field.
     */
    public static function hasActiveCoreBirthdayField(): bool
    {
        if (! Schema::hasTable('followup_customer_fields')) {
            return false;
        }

        return FollowupCustomerField::query()
            ->where('is_active', true)
            ->where(function ($query): void {
                $query->whereIn('key', ['birth_date', 'birthday', 'age', 'date_of_birth', 'player_birthday'])
                    ->orWhere(function ($sub): void {
                        $sub->where('type', 'date')
                            ->where(function ($nested): void {
                                $nested->where('key', 'like', '%birth%')
                                    ->orWhere('key', 'like', '%age%');
                            });
                    });
            })
            ->exists();
    }

    /**
     * Check if any pipeline stage contains an active birthday or age field.
     */
    public static function hasActiveStageBirthdayField(): bool
    {
        if (! Schema::hasTable('pipeline_stage_fields')) {
            return false;
        }

        return PipelineStageField::query()
            ->where('is_active', true)
            ->where(function ($query): void {
                $query->whereIn('key', ['birth_date', 'birthday', 'age', 'date_of_birth', 'player_birthday'])
                    ->orWhere('binding_target', 'birth_date')
                    ->orWhere(function ($sub): void {
                        $sub->where('type', 'date')
                            ->where(function ($nested): void {
                                $nested->where('key', 'like', '%birth%')
                                    ->orWhere('key', 'like', '%age%');
                            });
                    });
            })
            ->exists();
    }

    /**
     * Get active birthday sources information for UI diagnostics.
     *
     * @return array{core: array<string>, stages: array<string>}
     */
    public static function getActiveSources(): array
    {
        $sources = [
            'core' => [],
            'stages' => [],
        ];

        try {
            if (Schema::hasTable('followup_customer_fields')) {
                $sources['core'] = FollowupCustomerField::query()
                    ->where('is_active', true)
                    ->where(function ($query): void {
                        $query->whereIn('key', ['birth_date', 'birthday', 'age', 'date_of_birth', 'player_birthday'])
                            ->orWhere(function ($sub): void {
                                $sub->where('type', 'date')
                                    ->where(function ($nested): void {
                                        $nested->where('key', 'like', '%birth%')
                                            ->orWhere('key', 'like', '%age%');
                                    });
                            });
                    })
                    ->pluck('label_ar')
                    ->all();
            }

            if (Schema::hasTable('pipeline_stage_fields')) {
                $sources['stages'] = PipelineStageField::query()
                    ->with('stage')
                    ->where('is_active', true)
                    ->where(function ($query): void {
                        $query->whereIn('key', ['birth_date', 'birthday', 'age', 'date_of_birth', 'player_birthday'])
                            ->orWhere('binding_target', 'birth_date')
                            ->orWhere(function ($sub): void {
                                $sub->where('type', 'date')
                                    ->where(function ($nested): void {
                                        $nested->where('key', 'like', '%birth%')
                                            ->orWhere('key', 'like', '%age%');
                                    });
                            });
                    })
                    ->get()
                    ->map(fn ($f) => ($f->stage?->name_ar ?? 'مرحلة') . ' (' . ($f->label_ar ?: $f->key) . ')')
                    ->all();
            }
        } catch (\Throwable) {
            // Keep empty on error
        }

        return $sources;
    }

    /**
     * Flush memoized status.
     */
    public static function flushCache(): void
    {
        self::$cachedEnabled = null;
    }
}
