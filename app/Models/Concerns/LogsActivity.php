<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Model;

trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(function (Model $model): void {
            if ($model->shouldSkipActivityLogging()) {
                return;
            }

            $module = $model->getActivityLogModule();
            $action = $model->getActivityLogAction('created');
            $label = $model->getActivityLogLabel();
            $desc = app()->getLocale() === 'en'
                ? "Created {$label}"
                : "تم إنشاء {$label}";

            ActivityLogger::log(
                action: $action,
                module: $module,
                description: $desc,
                subject: $model,
                properties: [
                    'attributes' => $model->attributesToArray(),
                ],
            );
        });

        static::updated(function (Model $model): void {
            if ($model->shouldSkipActivityLogging()) {
                return;
            }

            $ignored = array_merge([
                'updated_at',
                'created_at',
                'remember_token',
                'password',
            ], $model->getActivityLogIgnoredFields());

            $changes = [];
            foreach ($model->getDirty() as $key => $newValue) {
                if (in_array($key, $ignored, true)) {
                    continue;
                }

                $oldValue = $model->getOriginal($key);

                // Resolve human-readable values for foreign keys like lead_status_id or assigned_user_id
                if ($key === 'lead_status_id') {
                    $oldStatus = $oldValue ? \App\Models\LeadStatus::find($oldValue) : null;
                    $newStatus = $newValue ? \App\Models\LeadStatus::find($newValue) : null;
                    $oldName = (app()->getLocale() === 'en' && !empty($oldStatus?->name_en)) ? $oldStatus->name_en : ($oldStatus?->name_ar ?? (string) $oldValue);
                    $newName = (app()->getLocale() === 'en' && !empty($newStatus?->name_en)) ? $newStatus->name_en : ($newStatus?->name_ar ?? (string) $newValue);

                    $changes['status'] = [
                        'old' => $oldName,
                        'new' => $newName,
                    ];
                    continue;
                }

                if ($key === 'assigned_user_id') {
                    $oldUser = $oldValue ? \App\Models\User::find($oldValue) : null;
                    $newUser = $newValue ? \App\Models\User::find($newValue) : null;

                    $changes['assigned_user'] = [
                        'old' => $oldUser?->name ?? (string) $oldValue,
                        'new' => $newUser?->name ?? (string) $newValue,
                    ];
                    continue;
                }

                $changes[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }

            if (empty($changes)) {
                return;
            }

            $module = $model->getActivityLogModule();
            $action = $model->getActivityLogAction('updated');
            $label = $model->getActivityLogLabel();
            $desc = app()->getLocale() === 'en'
                ? "Updated {$label}"
                : "تم تعديل بيانات {$label}";

            ActivityLogger::log(
                action: $action,
                module: $module,
                description: $desc,
                subject: $model,
                properties: [
                    'changes' => $changes,
                ],
            );
        });

        static::deleted(function (Model $model): void {
            if ($model->shouldSkipActivityLogging()) {
                return;
            }

            $module = $model->getActivityLogModule();
            $action = $model->getActivityLogAction('deleted');
            $label = $model->getActivityLogLabel();
            $desc = app()->getLocale() === 'en'
                ? "Deleted {$label}"
                : "تم حذف {$label}";

            ActivityLogger::log(
                action: $action,
                module: $module,
                description: $desc,
                subject: $model,
            );
        });
    }

    public function getActivityLogModule(): string
    {
        return strtolower(class_basename($this));
    }

    public function getActivityLogAction(string $event): string
    {
        return $this->getActivityLogModule() . '.' . $event;
    }

    public function getActivityLogLabel(): string
    {
        $name = $this->name ?? ($this->name_ar ?? ($this->title ?? class_basename($this)));
        return (string) $name;
    }

    public function getActivityLogIgnoredFields(): array
    {
        return [];
    }

    public function shouldSkipActivityLogging(): bool
    {
        return false;
    }
}
