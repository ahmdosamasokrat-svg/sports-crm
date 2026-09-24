<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppointmentAttendanceRecord extends Model
{
    protected $fillable = [
        'lead_id',
        'branch_id',
        'outcome',
        'appointment_date',
        'appointment_time',
        'activity',
        'branch_name_ar',
        'branch_name_en',
        'coach',
        'notes',
        'recorded_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'appointment_date' => 'date',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }
}
