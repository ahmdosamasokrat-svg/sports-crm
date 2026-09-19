<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Throwable;

class ActivityLogger
{
    public static function log(
        string $action,
        string $module,
        string $description,
        ?Model $subject = null,
        ?array $properties = null,
        ?User $actor = null
    ): ?ActivityLog {
        try {
            $user = $actor ?? Auth::user();
            $request = request();

            $subjectType = $subject ? $subject->getMorphClass() : null;
            $subjectId = $subject ? $subject->getKey() : null;
            $subjectLabel = self::resolveSubjectLabel($subject);

            return ActivityLog::query()->create([
                'user_id' => $user?->id,
                'user_name' => $user?->name ?? ($user?->username ?? 'النظام'),
                'action' => $action,
                'module' => $module,
                'subject_type' => $subjectType,
                'subject_id' => $subjectId,
                'subject_label' => $subjectLabel,
                'description' => $description,
                'properties' => $properties,
                'ip_address' => $request?->ip(),
                'user_agent' => $request?->userAgent(),
                'created_at' => now(),
            ]);
        } catch (Throwable) {
            // Fail silently to never break core business execution
            return null;
        }
    }

    private static function resolveSubjectLabel(?Model $subject): ?string
    {
        if ($subject === null) {
            return null;
        }

        if (isset($subject->name) && is_string($subject->name)) {
            return $subject->name;
        }

        if (isset($subject->title) && is_string($subject->title)) {
            return $subject->title;
        }

        if (isset($subject->name_ar) && is_string($subject->name_ar)) {
            return $subject->name_ar;
        }

        if (isset($subject->quotation_number) && is_string($subject->quotation_number)) {
            return $subject->quotation_number;
        }

        return class_basename($subject) . ' #' . $subject->getKey();
    }
}
