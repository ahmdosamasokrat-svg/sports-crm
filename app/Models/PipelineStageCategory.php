<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
class PipelineStageCategory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name_ar',
        'name_en',
        'description_ar',
        'color',
        'icon',
        'position',
        'is_active',
        'auto_transfer_enabled',
        'auto_transfer_action',
        'trigger_stage_id',
        'trigger_status_id',
        'target_stage_id',
        'target_status_id',
    ];
    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'is_active' => 'boolean',
            'auto_transfer_enabled' => 'boolean',
            'trigger_stage_id' => 'integer',
            'trigger_status_id' => 'integer',
            'target_stage_id' => 'integer',
            'target_status_id' => 'integer',
            'deleted_at' => 'datetime',
        ];
    }
    public function stages(): HasMany
    {
        return $this->hasMany(PipelineStage::class, 'pipeline_stage_category_id')
            ->orderBy('position')
            ->orderBy('id');
    }

    public function activeStages(): HasMany
    {
        return $this->stages()
            ->where('is_active', true);
    }

    public function permittedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function triggerStage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'trigger_stage_id');
    }

    public function triggerStatus(): BelongsTo
    {
        return $this->belongsTo(LeadStatus::class, 'trigger_status_id');
    }

    public function targetStage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'target_stage_id');
    }

    public function targetStatus(): BelongsTo
    {
        return $this->belongsTo(LeadStatus::class, 'target_status_id');
    }

    public function localizedName(?string $locale = null): string
    {
        $loc = $locale ?? app()->getLocale();

        if ($loc === 'en' && ! empty($this->name_en)) {
            return (string) $this->name_en;
        }

        return (string) $this->name_ar;
    }
}
