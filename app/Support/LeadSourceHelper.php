<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class LeadSourceHelper
{
    /**
     * Get all sources combining configured LeadSource records and existing Lead values.
     *
     * @return Collection<int, string>
     */
    public static function getAllSources(?User $actor = null): Collection
    {
        $sources = collect();

        // 1. Get from configured LeadSource GUI table if table exists
        if (Schema::hasTable('lead_sources')) {
            $configured = LeadSource::query()
                ->active()
                ->ordered()
                ->pluck('name_ar');
            $sources = $sources->concat($configured);
        }

        // 2. Add any existing distinct sources from leads
        $leadQuery = Lead::query();
        if ($actor) {
            $leadQuery->accessibleTo($actor);
        }
        $existing = $leadQuery->whereNotNull('source')
            ->where('source', '<>', '')
            ->distinct()
            ->pluck('source');

        return $sources->concat($existing)->filter()->unique()->values();
    }
}
