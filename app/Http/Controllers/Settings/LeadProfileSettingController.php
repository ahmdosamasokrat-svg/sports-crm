<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\LeadProfileSetting;
use App\Services\ActivityLogger;
use App\Support\CrmDatabaseGuard;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LeadProfileSettingController extends Controller
{
    public function index(): View
    {
        CrmDatabaseGuard::ensureConnected();

        $setting = LeadProfileSetting::current();
        $tabs = $setting->getOrderedTabs();

        return view('settings.lead-profile.index', [
            'setting' => $setting,
            'tabs' => $tabs,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        CrmDatabaseGuard::ensureConnected();

        $validated = $request->validate([
            'layout_mode' => ['required', 'string', 'in:hybrid,full_width'],
            'default_tab' => ['required', 'string'],
            'tabs' => ['required', 'array'],
            'tabs.*.key' => ['required', 'string'],
            'tabs.*.position' => ['required', 'integer', 'min:1', 'max:50'],
            'tabs.*.is_enabled' => ['nullable'],
            'tabs.*.label_ar' => ['nullable', 'string', 'max:150'],
            'tabs.*.label_en' => ['nullable', 'string', 'max:150'],
        ]);

        $defaultIcons = LeadProfileSetting::defaultIcons();
        $defaultConfig = collect(LeadProfileSetting::defaultTabsConfig())->keyBy('key');

        $updatedTabs = [];
        $enabledKeys = [];

        foreach ($validated['tabs'] as $tabInput) {
            $key = (string) ($tabInput['key'] ?? '');
            if (! $defaultConfig->has($key)) {
                continue;
            }

            $default = $defaultConfig->get($key);
            $isEnabled = isset($tabInput['is_enabled']) && (bool) $tabInput['is_enabled'];
            $position = (int) ($tabInput['position'] ?? $default['position']);
            $labelAr = ! empty($tabInput['label_ar']) ? trim((string) $tabInput['label_ar']) : $default['label_ar'];
            $labelEn = ! empty($tabInput['label_en']) ? trim((string) $tabInput['label_en']) : $default['label_en'];

            if ($isEnabled) {
                $enabledKeys[] = $key;
            }

            $updatedTabs[] = [
                'key' => $key,
                'is_enabled' => $isEnabled,
                'position' => $position,
                'label_ar' => $labelAr,
                'label_en' => $labelEn,
                'icon' => $defaultIcons[$key] ?? 'bi-folder',
            ];
        }

        // Guarantee at least one tab is enabled
        if (empty($enabledKeys)) {
            foreach ($updatedTabs as &$tab) {
                if ($tab['key'] === 'timeline') {
                    $tab['is_enabled'] = true;
                    $enabledKeys[] = 'timeline';
                    break;
                }
            }
            unset($tab);
        }

        // Sort by position
        usort($updatedTabs, static fn (array $a, array $b): int => $a['position'] <=> $b['position']);

        $defaultTab = (string) $validated['default_tab'];
        if (! in_array($defaultTab, $enabledKeys, true)) {
            $defaultTab = $enabledKeys[0] ?? 'timeline';
        }

        $setting = LeadProfileSetting::current();
        $setting->update([
            'layout_mode' => $validated['layout_mode'],
            'default_tab' => $defaultTab,
            'tabs_config' => $updatedTabs,
        ]);

        LeadProfileSetting::flushCache();

        ActivityLogger::log(
            action: 'settings.lead_profile.updated',
            module: 'settings',
            description: app()->getLocale() === 'en'
                ? 'Updated lead profile layout and tabs configuration'
                : 'تم تحديث تخطيط وتبويبات ملف العميل',
            properties: [
                'layout_mode' => $setting->layout_mode,
                'default_tab' => $setting->default_tab,
                'enabled_tabs_count' => count($enabledKeys),
            ],
            actor: $request->user(),
        );

        return to_route('v2.settings.lead_profile.index')
            ->with('success', __('crm.lead_profile_settings_saved') ?? 'تم حفظ إعدادات وتنسيق ملف العميل بنجاح.');
    }
}
