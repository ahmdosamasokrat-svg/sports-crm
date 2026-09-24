<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Support\CrmDatabaseGuard;
use App\Support\ReferralFieldSchema;
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

        if (! ReferralFieldSchema::isEnabled()) {
            return response()->json([
                'success' => false,
                'message' => __('crm.referrals_disabled_notice') ?: 'نظام تسجيل الإحالات معطل حالياً من الإعدادات.',
            ], 403);
        }
        try {
            $validated = ReferralFieldSchema::validate($request);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first() ?: 'خطأ في التحقق من البيانات.',
                'errors' => $e->errors(),
            ], 422);
        }

        $actor = $request->user();

        $referralLead = DB::transaction(function () use ($lead, $validated, $actor): Lead {
            return ReferralFieldSchema::persist($lead, $validated, $actor);
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
