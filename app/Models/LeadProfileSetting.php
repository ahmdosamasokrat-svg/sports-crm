<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class LeadProfileSetting extends Model
{
    public const CACHE_KEY = 'crm.lead_profile_settings.attributes';

    private static ?self $memoized = null;

    protected $fillable = [
        'layout_mode',
        'default_tab',
        'tabs_config',
        'filters_config',
    ];

    protected function casts(): array
    {
        return [
            'tabs_config' => 'array',
            'filters_config' => 'array',
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
            $setting = new self;
            $setting->setRawAttributes($raw, true);
            $setting->exists = true;
            self::$memoized = $setting;

            return $setting;
        }

        $setting = self::query()->find(1);

        if (! $setting) {
            $setting = self::query()->create([
                'id' => 1,
                'layout_mode' => 'hybrid',
                'default_tab' => 'timeline',
                'tabs_config' => self::defaultTabsConfig(),
                'filters_config' => self::defaultFiltersConfig(),
            ]);
        }

        Cache::forever(self::CACHE_KEY, $setting->getAttributes());
        self::$memoized = $setting;

        return $setting;
    }

    /**
     * @return array<int, array{key: string, is_enabled: bool, position: int, label_ar: string, label_en: string, icon: string}>
     */
    public static function defaultTabsConfig(): array
    {
        return [
            [
                'key' => 'timeline',
                'is_enabled' => true,
                'position' => 1,
                'label_ar' => 'سجل النشاط والمتابعات',
                'label_en' => 'Activity Timeline',
                'icon' => 'bi-clock-history',
            ],
            [
                'key' => 'client_data',
                'is_enabled' => true,
                'position' => 2,
                'label_ar' => 'بيانات العميل',
                'label_en' => 'Customer Info',
                'icon' => 'bi-person-vcard',
            ],
            [
                'key' => 'stage_data',
                'is_enabled' => true,
                'position' => 3,
                'label_ar' => 'بيانات المرحلة والأسئلة',
                'label_en' => 'Stage Questions',
                'icon' => 'bi-ui-checks-grid',
            ],
            [
                'key' => 'appointments',
                'is_enabled' => true,
                'position' => 4,
                'label_ar' => 'المواعيد والحضور',
                'label_en' => 'Appointments & Attendance',
                'icon' => 'bi-calendar2-check',
            ],
            [
                'key' => 'voip_calls',
                'is_enabled' => true,
                'position' => 5,
                'label_ar' => 'سجل المكالمات VoIP',
                'label_en' => 'VoIP Calls',
                'icon' => 'bi-telephone-inbound',
            ],
            [
                'key' => 'documents',
                'is_enabled' => true,
                'position' => 6,
                'label_ar' => 'المستندات وعروض الأسعار',
                'label_en' => 'Documents & Quotes',
                'icon' => 'bi-folder2-open',
            ],
            [
                'key' => 'referrals',
                'is_enabled' => true,
                'position' => 7,
                'label_ar' => 'الإحالات والأسرة',
                'label_en' => 'Referrals & Family',
                'icon' => 'bi-people',
            ],
            [
                'key' => 'notes',
                'is_enabled' => true,
                'position' => 8,
                'label_ar' => 'الملاحظات',
                'label_en' => 'Notes',
                'icon' => 'bi-chat-left-text',
            ],
        ];
    }

    /**
     * Default filters configuration in the recommended logical CRM order.
     *
     * @return array<int, array{key: string, is_enabled: bool, is_multiselect: bool, position: int, label_ar: string, label_en: string, icon: string}>
     */
    public static function defaultFiltersConfig(): array
    {
        return [
            [
                'key' => 'q',
                'is_enabled' => true,
                'is_multiselect' => false,
                'position' => 1,
                'label_ar' => 'بحث سريع',
                'label_en' => 'Quick Search',
                'icon' => 'bi-search',
            ],
            [
                'key' => 'status',
                'is_enabled' => true,
                'is_multiselect' => true,
                'position' => 2,
                'label_ar' => 'الحالة',
                'label_en' => 'Status',
                'icon' => 'bi-tag',
            ],
            [
                'key' => 'source',
                'is_enabled' => true,
                'is_multiselect' => true,
                'position' => 3,
                'label_ar' => 'المصدر',
                'label_en' => 'Source',
                'icon' => 'bi-diagram-2',
            ],
            [
                'key' => 'branch',
                'is_enabled' => true,
                'is_multiselect' => false,
                'position' => 4,
                'label_ar' => 'الفرع',
                'label_en' => 'Branch',
                'icon' => 'bi-geo-alt',
            ],
            [
                'key' => 'employee',
                'is_enabled' => true,
                'is_multiselect' => true,
                'position' => 5,
                'label_ar' => 'الموظف المسند إليه',
                'label_en' => 'Assigned Employee',
                'icon' => 'bi-person-check',
            ],
            [
                'key' => 'temperature',
                'is_enabled' => true,
                'is_multiselect' => false,
                'position' => 6,
                'label_ar' => 'حرارة العميل',
                'label_en' => 'Lead Temperature',
                'icon' => 'bi-thermometer-half',
            ],
            [
                'key' => 'guardian_id',
                'is_enabled' => true,
                'is_multiselect' => false,
                'position' => 7,
                'label_ar' => 'ولي الأمر (Guardian)',
                'label_en' => 'Guardian',
                'icon' => 'bi-people',
            ],
            [
                'key' => 'follow_up',
                'is_enabled' => true,
                'is_multiselect' => false,
                'position' => 8,
                'label_ar' => 'المتابعة القادمة',
                'label_en' => 'Next Follow-up',
                'icon' => 'bi-calendar-event',
            ],
            [
                'key' => 'sort',
                'is_enabled' => true,
                'is_multiselect' => false,
                'position' => 9,
                'label_ar' => 'الترتيب',
                'label_en' => 'Sort Order',
                'icon' => 'bi-sort-down',
            ],
            [
                'key' => 'choose_fields',
                'is_enabled' => true,
                'is_multiselect' => false,
                'position' => 10,
                'label_ar' => 'تحديد الأعمدة والفلاتر',
                'label_en' => 'Columns & Field Chooser',
                'icon' => 'bi-layout-three-columns',
            ],
        ];
    }

    /**
     * Get all ordered filters with normalized attributes.
     *
     * @return array<int, array{key: string, is_enabled: bool, is_multiselect: bool, position: int, label_ar: string, label_en: string, label: string, icon: string}>
     */
    public function getOrderedFilters(?string $locale = null): array
    {
        $locale = $locale ?? app()->getLocale();
        $stored = is_array($this->filters_config) ? $this->filters_config : [];
        $defaults = self::defaultFiltersConfig();
        $defaultsByKey = collect($defaults)->keyBy('key');
        $storedByKey = collect($stored)->keyBy('key');

        $merged = collect();

        foreach ($defaultsByKey as $key => $defaultFilter) {
            $userFilter = $storedByKey->get($key, []);
            $labelAr = ! empty($userFilter['label_ar']) ? (string) $userFilter['label_ar'] : $defaultFilter['label_ar'];
            $labelEn = ! empty($userFilter['label_en']) ? (string) $userFilter['label_en'] : $defaultFilter['label_en'];
            $icon = ! empty($userFilter['icon']) ? (string) $userFilter['icon'] : $defaultFilter['icon'];
            $position = isset($userFilter['position']) ? (int) $userFilter['position'] : (int) $defaultFilter['position'];
            $isEnabled = isset($userFilter['is_enabled']) ? (bool) $userFilter['is_enabled'] : (bool) $defaultFilter['is_enabled'];
            $isMultiselect = isset($userFilter['is_multiselect']) ? (bool) $userFilter['is_multiselect'] : (bool) $defaultFilter['is_multiselect'];

            $merged->push([
                'key' => $key,
                'is_enabled' => $isEnabled,
                'is_multiselect' => $isMultiselect,
                'position' => $position,
                'label_ar' => $labelAr,
                'label_en' => $labelEn,
                'label' => $locale === 'en' ? $labelEn : $labelAr,
                'icon' => $icon,
            ]);
        }

        return $merged->sortBy('position')->values()->all();
    }

    /**
     * Get only active (enabled) filters ordered by position.
     *
     * @return array<int, array{key: string, is_enabled: bool, is_multiselect: bool, position: int, label_ar: string, label_en: string, label: string, icon: string}>
     */
    public function getActiveFilters(?string $locale = null): array
    {
        return array_values(array_filter(
            $this->getOrderedFilters($locale),
            static fn (array $filter): bool => $filter['is_enabled'] === true
        ));
    }

    /**
     * @return array<string, string>
     */
    public static function defaultIcons(): array
    {
        return [
            'timeline' => 'bi-clock-history',
            'client_data' => 'bi-person-vcard',
            'stage_data' => 'bi-ui-checks-grid',
            'appointments' => 'bi-calendar2-check',
            'voip_calls' => 'bi-telephone-inbound',
            'documents' => 'bi-folder2-open',
            'referrals' => 'bi-people',
            'notes' => 'bi-chat-left-text',
        ];
    }

    /**
     * Get all ordered tabs with normalized attributes.
     *
     * @return array<int, array{key: string, is_enabled: bool, position: int, label_ar: string, label_en: string, label: string, icon: string}>
     */
    public function getOrderedTabs(?string $locale = null): array
    {
        $locale = $locale ?? app()->getLocale();
        $stored = is_array($this->tabs_config) ? $this->tabs_config : [];
        $defaults = self::defaultTabsConfig();
        $defaultsByKey = collect($defaults)->keyBy('key');
        $storedByKey = collect($stored)->keyBy('key');

        $merged = collect();

        foreach ($defaultsByKey as $key => $defaultTab) {
            $userTab = $storedByKey->get($key, []);
            $labelAr = ! empty($userTab['label_ar']) ? (string) $userTab['label_ar'] : $defaultTab['label_ar'];
            $labelEn = ! empty($userTab['label_en']) ? (string) $userTab['label_en'] : $defaultTab['label_en'];
            $icon = ! empty($userTab['icon']) ? (string) $userTab['icon'] : $defaultTab['icon'];
            $position = isset($userTab['position']) ? (int) $userTab['position'] : (int) $defaultTab['position'];
            $isEnabled = isset($userTab['is_enabled']) ? (bool) $userTab['is_enabled'] : (bool) $defaultTab['is_enabled'];

            $merged->push([
                'key' => $key,
                'is_enabled' => $isEnabled,
                'position' => $position,
                'label_ar' => $labelAr,
                'label_en' => $labelEn,
                'label' => $locale === 'en' ? $labelEn : $labelAr,
                'icon' => $icon,
            ]);
        }

        return $merged->sortBy('position')->values()->all();
    }

    /**
     * Get only active (enabled) tabs ordered by position.
     *
     * @return array<int, array{key: string, is_enabled: bool, position: int, label_ar: string, label_en: string, label: string, icon: string}>
     */
    public function getActiveTabs(?string $locale = null): array
    {
        return array_values(array_filter(
            $this->getOrderedTabs($locale),
            static fn (array $tab): bool => $tab['is_enabled'] === true
        ));
    }
}
