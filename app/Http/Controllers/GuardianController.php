<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Guardian;
use App\Models\Lead;
use App\Support\CrmDatabaseGuard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GuardianController extends Controller
{
    private function assertCrmDatabase(): void
    {
        CrmDatabaseGuard::ensureConnected();
    }

    /**
     * Search guardians by query string for searchable dropdown.
     */
    public function search(Request $request): JsonResponse
    {
        $this->assertCrmDatabase();

        $query = trim((string) $request->query('q', ''));

        $guardians = Guardian::query()
            ->when($query !== '', function ($q) use ($query): void {
                $q->where(function ($sub) use ($query): void {
                    $sub->where('name', 'LIKE', "%{$query}%")
                        ->orWhere('phone', 'LIKE', "%{$query}%")
                        ->orWhere('secondary_phone', 'LIKE', "%{$query}%");
                });
            })
            ->withCount('players')
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'phone', 'relationship']);

        return response()->json([
            'success' => true,
            'guardians' => $guardians,
        ]);
    }

    /**
     * Store a new guardian and optionally link directly to a lead.
     */
    public function store(Request $request): JsonResponse
    {
        $this->assertCrmDatabase();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'secondary_phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:190'],
            'relationship' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'lead_id' => ['nullable', 'integer', 'exists:leads,id'],
        ]);

        $guardian = DB::transaction(function () use ($validated, $request): Guardian {
            $created = Guardian::query()->create([
                'name' => trim($validated['name']),
                'phone' => ! empty($validated['phone']) ? trim($validated['phone']) : null,
                'secondary_phone' => ! empty($validated['secondary_phone']) ? trim($validated['secondary_phone']) : null,
                'email' => ! empty($validated['email']) ? trim($validated['email']) : null,
                'relationship' => ! empty($validated['relationship']) ? trim($validated['relationship']) : 'أب',
                'notes' => ! empty($validated['notes']) ? trim($validated['notes']) : null,
                'created_by_user_id' => $request->user()?->id,
            ]);

            if (! empty($validated['lead_id'])) {
                Lead::query()->where('id', $validated['lead_id'])->update(['guardian_id' => $created->id]);
            }

            return $created;
        });

        $siblings = [];
        if (! empty($validated['lead_id'])) {
            $lead = Lead::query()->find($validated['lead_id']);
            $siblings = $lead?->siblings->map(fn (Lead $s) => [
                'id' => $s->id,
                'name' => $s->name,
                'stage' => $s->status?->stage?->localizedName() ?? '—',
                'category' => $s->status?->stage?->category?->localizedName(),
            ])->toArray() ?? [];
        }

        return response()->json([
            'success' => true,
            'message' => __('crm.guardian_saved_successfully') ?? 'تم حفظ ولي الأمر بنجاح.',
            'guardian' => [
                'id' => $guardian->id,
                'name' => $guardian->name,
                'phone' => $guardian->phone,
                'relationship' => $guardian->relationship,
            ],
            'siblings' => $siblings,
        ]);
    }

    /**
     * Link an existing guardian to a lead.
     */
    public function link(Request $request, Lead $lead): JsonResponse
    {
        $this->assertCrmDatabase();

        $validated = $request->validate([
            'guardian_id' => ['required', 'integer', 'exists:guardians,id'],
        ]);

        $lead->update(['guardian_id' => $validated['guardian_id']]);
        $guardian = Guardian::query()->findOrFail($validated['guardian_id']);

        $siblings = $lead->fresh()->siblings()->get()->map(fn (Lead $s) => [
            'id' => $s->id,
            'name' => $s->name,
            'stage' => $s->status?->stage?->localizedName() ?? '—',
            'category' => $s->status?->stage?->category?->localizedName(),
        ])->values()->toArray();

        return response()->json([
            'success' => true,
            'message' => __('crm.guardian_linked_successfully') ?? 'تم ربط ولي الأمر باللاعب بنجاح.',
            'guardian' => [
                'id' => $guardian->id,
                'name' => $guardian->name,
                'phone' => $guardian->phone,
                'relationship' => $guardian->relationship,
            ],
            'siblings' => $siblings,
        ]);
    }

    /**
     * Unlink guardian from a lead.
     */
    public function unlink(Lead $lead): JsonResponse
    {
        $this->assertCrmDatabase();

        $lead->update(['guardian_id' => null]);

        return response()->json([
            'success' => true,
            'message' => __('crm.guardian_unlinked_successfully') ?? 'تم فك ربط ولي الأمر عن اللاعب.',
        ]);
    }
}
