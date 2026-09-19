<?php

declare(strict_types=1);

use App\Support\CrmDatabaseGuard;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        CrmDatabaseGuard::ensureConnected();

        DB::transaction(function (): void {
            $existingBranchCount = DB::table('branches')->count();
            $fallbackBranch = DB::table('branches')->where('code', 'main')->first();

            if ($existingBranchCount === 0 && $fallbackBranch === null) {
                $now = now();
                $fallbackBranchId = DB::table('branches')->insertGetId([
                    'name_ar' => 'الفرع الرئيسي',
                    'name_en' => 'Main Branch',
                    'code' => 'main',
                    'phone' => null,
                    'address' => null,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            } else {
                $fallbackBranchId = $fallbackBranch?->id ?? (int) DB::table('branches')->value('id');
            }

            if ($fallbackBranchId) {
                // Assign legacy users with null branch to fallback branch
                DB::table('users')
                    ->whereNull('branch_id')
                    ->update(['branch_id' => $fallbackBranchId]);

                // Assign legacy leads with null branch to fallback branch
                DB::table('leads')
                    ->whereNull('branch_id')
                    ->update(['branch_id' => $fallbackBranchId]);
            }
        });
    }

    public function down(): void
    {
        CrmDatabaseGuard::ensureConnected();

        // Schema down() in previous migration drops the branch_id column safely.
    }
};
