<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadStatus;
use App\Models\PipelineStage;
use App\Security\CrmPermission;
use App\Support\CrmDatabaseGuard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ReferralController extends Controller
{
    /**
     * Store a new referral prospect linked to the referring subscriber lead.
     */
    public function store(Request $request, Lead $lead): JsonResponse
    {
        $this->assertCrmDatabase();

        Gate::authorize('create', Lead::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'activity' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // Default initial status: earliest status of primary stage or active stage
        $initialStatusId = LeadStatus::query()
            ->whereHas('stage', fn ($q) => $q->where('is_active', true))
            ->orderBy('position')
            ->orderBy('id')
            ->value('id');

        $actor = $request->user();

        $referralLead = DB::transaction(function () use ($validated, $lead, $actor, $initialStatusId) {
            return Lead::query()->create([
                'name' => trim($validated['name']),
                'phone' => trim($validated['phone']),
                'activity' => $validated['activity'] ?? $lead->activity,
                'source' => 'referral',
                'branch_id' => $lead->branch_id ?? $actor->branch_id,
                'lead_status_id' => $initialStatusId,
                'assigned_user_id' => $actor->id,
                'assigned_employee' => $actor->name,
                'created_by' => $actor->name,
                'created_by_user_id' => $actor->id,
                'referred_by_lead_id' => $lead->id,
                'notes' => ! empty($validated['notes'])
                    ? "إحالة من المشترك: {$lead->name} (#ID: {$lead->id})\n" . trim($validated['notes'])
                    : "إحالة من المشترك: {$lead->name} (#ID: {$lead->id})",
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الإحالة بنجاح وإنشاء عميل محتمل جديد مرتبط بالمشترك.',
            'referral' => [
                'id' => $referralLead->id,
                'name' => $referralLead->name,
                'phone' => $referralLead->phone,
                'activity' => $referralLead->activity,
                'stage' => $referralLead->status?->stage?->localizedName() ?? '—',
                'status' => $referralLead->status?->name_ar ?? '—',
                'created_at' => $referralLead->created_at?->format('Y-m-d h:i A'),
                'url' => route('v2.leads.show', $referralLead),
            ],
            'referrer' => [
                'id' => $lead->id,
                'name' => $lead->name,
                'total_referrals' => $lead->referrals()->count(),
            ],
        ]);
    }

    private function assertCrmDatabase(): void
    {
        CrmDatabaseGuard::ensureConnected();
    }
}
