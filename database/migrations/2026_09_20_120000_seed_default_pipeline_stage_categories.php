<?php

declare(strict_types=1);

use App\Models\PipelineStage;
use App\Models\PipelineStageCategory;
use App\Support\CrmDatabaseGuard;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private function verifyDatabase(): void
    {
        CrmDatabaseGuard::ensureConnected();
    }

    public function up(): void
    {
        $this->verifyDatabase();

        DB::transaction(function (): void {
            if (PipelineStageCategory::query()->count() === 0) {
                $cat1 = PipelineStageCategory::query()->create([
                    'name_ar' => 'المبيعات والتواصل',
                    'name_en' => 'Sales & Outreach',
                    'description_ar' => 'استقبال العملاء الجدد ومحاولات التواصل والمتابعة الأولية',
                    'color' => '#3478f6',
                    'icon' => 'bi-person-lines-fill',
                    'position' => 1,
                    'is_active' => true,
                ]);

                $cat2 = PipelineStageCategory::query()->create([
                    'name_ar' => 'المفاوضات والعروض',
                    'name_en' => 'Proposals & Negotiation',
                    'description_ar' => 'إعداد وتقديم العروض التوضيحية وعروض الأسعار والتفاوض',
                    'color' => '#e59b16',
                    'icon' => 'bi-file-earmark-text-fill',
                    'position' => 2,
                    'is_active' => true,
                ]);

                $cat3 = PipelineStageCategory::query()->create([
                    'name_ar' => 'الإغلاق والتنفيذ',
                    'name_en' => 'Closing & Execution',
                    'description_ar' => 'توقيع العقود وتنفيذ وتسليم الخدمات واستلام الدفعات',
                    'color' => '#16a34a',
                    'icon' => 'bi-check2-circle',
                    'position' => 3,
                    'is_active' => true,
                ]);

                // Assign stages to categories
                PipelineStage::query()
                    ->whereIn('code', ['new', 'no_answer', 'interested', 'not_interested', 'stage_wl6ajmts'])
                    ->update(['pipeline_stage_category_id' => $cat1->id]);

                PipelineStage::query()
                    ->whereIn('code', ['meeting', 'quotation', 'discussion'])
                    ->update(['pipeline_stage_category_id' => $cat2->id]);

                PipelineStage::query()
                    ->whereIn('code', ['contract_closed', 'execution', 'stage_m309s3rs', 'stage_ft0k2snz'])
                    ->update(['pipeline_stage_category_id' => $cat3->id]);
            }
        });
    }

    public function down(): void
    {
        $this->verifyDatabase();

        DB::transaction(function (): void {
            PipelineStage::query()
                ->whereNotNull('pipeline_stage_category_id')
                ->update(['pipeline_stage_category_id' => null]);

            PipelineStageCategory::query()
                ->whereIn('name_ar', [
                    'المبيعات والتواصل',
                    'المفاوضات والعروض',
                    'الإغلاق والتنفيذ',
                ])
                ->forceDelete();
        });
    }
};
