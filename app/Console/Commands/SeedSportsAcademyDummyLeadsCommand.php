<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\CrmDatabaseGuard;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SeedSportsAcademyDummyLeadsCommand extends Command
{
    protected $signature = 'crm:seed-sports-dummy-leads {count=3000 : Number of dummy leads to generate}';
    protected $description = 'Seed thousands of realistic sports academy dummy leads across all pipelines with populated stage questions';

    private array $arabicFirstNames = [
        'أحمد', 'محمد', 'عمر', 'يوسف', 'حمزة', 'علي', 'إبراهيم', 'حسن', 'حسين', 'خالد',
        'كريم', 'طارق', 'سيف', 'آدم', 'ياسين', 'سليم', 'مازن', 'بلال', 'زياد', 'عبدالرحمن',
        'مريم', 'فاطمة', 'نور', 'سارة', 'ليلى', 'جنى', 'فريدة', 'ملك', 'سلمى', 'هنا',
        'ياسمين', 'حبيبة', 'كارما', 'تيا', 'لارا', 'تاليا', 'ندى', 'ريم', 'ريماس', 'روان'
    ];

    private array $arabicLastNames = [
        'الشريف', 'المنشاوي', 'الحداد', 'الباز', 'سليمان', 'العدوي', 'الصاوي', 'عفيفي', 'بركات', 'عاشور',
        'النمر', 'عمران', 'الخطيب', 'الشوربجي', 'سلامة', 'الغندور', 'النجار', 'رضوان', 'الشناوي', 'مرعي',
        'الشافعي', 'السيد', 'عبدالله', 'محمود', 'إسماعيل', 'صالح', 'فاروق', 'زهران', 'الديب', 'القاضي'
    ];

    private array $sports = [
        'كرة قدم', 'سباحة', 'جمباز', 'كاراتيه', 'كرة سلة', 'تنس', 'ملاكمة', 'تايكوندو'
    ];

    private array $sources = [
        'facebook', 'instagram', 'tiktok', 'google', 'whatsapp', 'walk-in', 'referral', 'excel_import'
    ];

    private array $campaigns = [
        'حملة أبطال الصيف 2026', 'إعلان تدريب السباحة المكثف', 'حملة براعم كرة القدم', 'إعلان الجمباز التأسيسي', 'حملة العودة للمدارس', 'عرض الأشقاء والأسرة'
    ];

    private array $temperatures = ['hot', 'warm', 'cold', 'unclassified'];

    public function handle(): int
    {
        CrmDatabaseGuard::ensureConnected();

        $count = (int) $this->argument('count');
        if ($count <= 0) {
            $count = 3000;
        }

        $this->info("🚀 Starting generation of {$count} Sports Academy leads across all pipelines...");

        // 1. Gather reference data
        $users = DB::table('users')->where('is_active', 1)->pluck('id')->all();
        if (empty($users)) {
            $users = [1];
        }

        $branches = DB::table('branches')->pluck('id')->all();
        $defaultBranchId = $branches[0] ?? 1;

        $stages = DB::table('pipeline_stages')
            ->whereNull('deleted_at')
            ->where('is_active', 1)
            ->get();

        if ($stages->isEmpty()) {
            $this->error("No active pipeline stages found!");
            return 1;
        }

        $statuses = DB::table('lead_statuses')
            ->whereNull('deleted_at')
            ->get()
            ->groupBy('pipeline_stage_id');

        $stageFields = DB::table('pipeline_stage_fields')
            ->whereNull('deleted_at')
            ->where('is_active', 1)
            ->get()
            ->groupBy('pipeline_stage_id');

        // 2. Pre-generate or find Guardians for Sibling Radar
        $this->info("Creating / ensuring realistic Guardians for Sibling Radar...");
        $guardianIds = [];
        $existingGuardians = DB::table('guardians')->pluck('id')->all();
        if (count($existingGuardians) < 400) {
            $guardianBatch = [];
            $needed = 400 - count($existingGuardians);
            for ($g = 0; $g < $needed; $g++) {
                $gFirst = $this->arabicFirstNames[array_rand($this->arabicFirstNames)];
                $gLast = $this->arabicLastNames[array_rand($this->arabicLastNames)];
                $guardianBatch[] = [
                    'name' => "ولي الأمر: {$gFirst} {$gLast}",
                    'relationship' => (rand(0, 1) === 0 ? 'أب' : 'أم'),
                    'phone' => '05' . rand(10000000, 99999999),
                    'secondary_phone' => '05' . rand(10000000, 99999999),
                    'notes' => 'أسرة رياضية مهتمة بتدريب الأبناء بانتظام',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('guardians')->insert($guardianBatch);
            $guardianIds = DB::table('guardians')->pluck('id')->all();
        } else {
            $guardianIds = $existingGuardians;
        }

        // 3. Process in Chunks of 500 for optimal memory & MySQL insert performance
        $chunkSize = 500;
        $totalChunks = (int) ceil($count / $chunkSize);
        $leadsCreated = 0;
        $valuesCreated = 0;
        $followupsCreated = 0;

        $now = Carbon::now();

        for ($chunk = 1; $chunk <= $totalChunks; $chunk++) {
            $currentChunkCount = min($chunkSize, $count - $leadsCreated);
            if ($currentChunkCount <= 0) {
                break;
            }

            $leadsBatch = [];
            $metaBatch = [];

            for ($i = 0; $i < $currentChunkCount; $i++) {
                $stage = $stages->random();
                $stageStatuses = $statuses->get($stage->id);
                $statusId = $stageStatuses ? $stageStatuses->random()->id : 1;

                $firstName = $this->arabicFirstNames[array_rand($this->arabicFirstNames)];
                $lastName = $this->arabicLastNames[array_rand($this->arabicLastNames)];
                $fullName = "{$firstName} {$lastName}";
                $sport = $this->sports[array_rand($this->sports)];
                $source = $this->sources[array_rand($this->sources)];
                $campaign = $this->campaigns[array_rand($this->campaigns)];
                $temp = $this->temperatures[array_rand($this->temperatures)];
                $assignedUserId = $users[array_rand($users)];

                // Age between 4 and 16 years old
                $birthDate = $now->copy()->subYears(rand(4, 16))->subMonths(rand(1, 11))->subDays(rand(1, 28))->toDateString();

                // Call attempts logic
                $callAttempts = ($stage->position > 1) ? rand(1, 4) : rand(0, 1);
                $firstContactedAt = ($callAttempts > 0) ? $now->copy()->subDays(rand(1, 30)) : null;
                $lastContactedAt = $firstContactedAt ? $firstContactedAt->copy()->addDays(rand(0, 5)) : null;

                // Next followup date (overdue, today, upcoming, or null)
                $followupRoll = rand(1, 10);
                if ($followupRoll <= 3) {
                    $nextFollowup = $now->copy()->subDays(rand(1, 7))->setTime(rand(9, 18), rand(0, 59)); // Overdue
                } elseif ($followupRoll <= 6) {
                    $nextFollowup = $now->copy()->setTime(rand(9, 18), rand(0, 59)); // Today
                } elseif ($followupRoll <= 8) {
                    $nextFollowup = $now->copy()->addDays(rand(1, 10))->setTime(rand(9, 18), rand(0, 59)); // Upcoming
                } else {
                    $nextFollowup = null; // No date
                }

                $guardianId = (!empty($guardianIds) && rand(1, 10) <= 7) ? $guardianIds[array_rand($guardianIds)] : null;

                $leadsBatch[] = [
                    'branch_id' => $defaultBranchId,
                    'guardian_id' => $guardianId,
                    'lead_status_id' => $statusId,
                    'name' => $fullName,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'phone' => '05' . rand(10000000, 99999999),
                    'email' => Str::slug($firstName . rand(100, 999)) . '@example.com',
                    'activity' => $sport,
                    'birth_date' => $birthDate,
                    'source' => $source,
                    'company_name' => $campaign,
                    'assigned_user_id' => $assignedUserId,
                    'assigned_employee' => 'موظف مبيعات',
                    'call_attempts_count' => $callAttempts,
                    'last_call_status' => ($callAttempts > 0 ? 'تم التواصل' : null),
                    'last_outcome_category' => ($callAttempts > 0 ? 'مؤهل' : null),
                    'first_contacted_at' => $firstContactedAt,
                    'last_contacted_at' => $lastContactedAt,
                    'next_follow_up_at' => $nextFollowup,
                    'custom_fields' => json_encode(['lead_temperature' => $temp]),
                    'created_at' => $now->copy()->subDays(rand(0, 60)),
                    'updated_at' => $now,
                ];

                $metaBatch[] = [
                    'stage_id' => $stage->id,
                    'status_id' => $statusId,
                    'assigned_user_id' => $assignedUserId,
                    'sport' => $sport,
                    'call_attempts' => $callAttempts,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                ];
            }

            // Insert chunk and retrieve IDs
            DB::table('leads')->insert($leadsBatch);
            $latestInsertedLeads = DB::table('leads')
                ->orderByDesc('id')
                ->limit($currentChunkCount)
                ->get(['id', 'branch_id', 'assigned_user_id'])
                ->reverse()
                ->values();

            $stageValuesBatch = [];
            $followupsBatch = [];

            foreach ($latestInsertedLeads as $idx => $leadRow) {
                $leadId = $leadRow->id;
                $meta = $metaBatch[$idx] ?? null;
                if (! $meta) continue;

                $stageId = $meta['stage_id'];
                $fieldsForStage = $stageFields->get($stageId);

                // Populate stage questions for this lead
                if ($fieldsForStage && $fieldsForStage->isNotEmpty()) {
                    foreach ($fieldsForStage as $field) {
                        $val = $this->generateFieldValue($field, $meta);
                        if ($val !== null) {
                            $stageValuesBatch[] = [
                                'lead_id' => $leadId,
                                'pipeline_stage_id' => $stageId,
                                'pipeline_stage_field_id' => $field->id,
                                'field_key' => $field->key,
                                'field_type' => $field->type,
                                'value' => (string) $val,
                                'created_by_user_id' => $meta['assigned_user_id'],
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                        }
                    }
                }

                // Add call followup log if attempts > 0
                if ($meta['call_attempts'] > 0) {
                    $followupsBatch[] = [
                        'lead_id' => $leadId,
                        'from_status_id' => $meta['status_id'],
                        'to_status_id' => $meta['status_id'],
                        'employee_name' => 'موظف مبيعات',
                        'user_id' => $meta['assigned_user_id'],
                        'communication_type' => 'call',
                        'call_attempt_number' => $meta['call_attempts'],
                        'call_status' => 'تم التواصل',
                        'outcome_category' => 'مؤهل',
                        'call_duration_seconds' => rand(45, 360),
                        'outcome' => 'تم التواصل مع ولي الأمر وشرح مواعيد التدريب وباقات النشاط الرياضي.',
                        'followed_up_at' => $now->copy()->subDays(rand(1, 15)),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
            if (! empty($stageValuesBatch)) {
                foreach (array_chunk($stageValuesBatch, 500) as $valChunk) {
                    DB::table('lead_stage_field_values')->insert($valChunk);
                }
                $valuesCreated += count($stageValuesBatch);
            }

            if (! empty($followupsBatch)) {
                foreach (array_chunk($followupsBatch, 500) as $fChunk) {
                    DB::table('lead_followups')->insert($fChunk);
                }
                $followupsCreated += count($followupsBatch);
            }

            $leadsCreated += $currentChunkCount;
            $this->line("Chunk {$chunk}/{$totalChunks} done. (Total leads: {$leadsCreated}, stage answers: {$valuesCreated})");
        }

        $this->info("✅ Successfully generated {$leadsCreated} dummy leads across all pipelines!");
        $this->info("📊 Total stage question values created: {$valuesCreated}");
        $this->info("📞 Total call followups logged: {$followupsCreated}");

        return 0;
    }

    private function generateFieldValue($field, array $meta): ?string
    {
        $key = $field->key;
        $type = $field->type;

        // Custom specific sports domain values
        return match ($key) {
            'player_name' => "اللاعب: {$meta['first_name']} {$meta['last_name']}",
            'activity', 'requested_activity' => $meta['sport'],
            'trial_date' => Carbon::now()->addDays(rand(-3, 7))->toDateString(),
            'trial_time' => sprintf('%02d:00', rand(15, 20)),
            'trial_status' => (['تم الحجز', 'تم التأكيد', 'حضر', 'لم يحضر'])[array_rand(['تم الحجز', 'تم التأكيد', 'حضر', 'لم يحضر'])],
            'trial_type' => 'تجربة تقييم مستوى',
            'attended' => (rand(0, 1) === 0 ? 'نعم' : 'لا'),
            'completed_trial' => 'نعم',
            'trial_outcome' => (['إيجابية', 'متوسطة'])[rand(0, 1)],
            'subscribed' => (rand(0, 1) === 0 ? 'نعم' : 'لا'),
            'reason_for_not_subscribing' => (['السعر مرتفع', 'المواعيد غير مناسبة', 'الموقع بعيد', 'المدرب'])[rand(0, 3)],
            'non_renewal_reason' => (['السعر مرتفع', 'توقف اللاعب عن النشاط الرياضي', 'ظروف دراسية أو امتحانات', 'المواعيد غير مناسبة', 'إصابة أو عارض صحي للاعب'])[rand(0, 4)],
            'parent_decision_status' => (['موافق على التجديد', 'يفكر ويقارن', 'طلب خصم إضافي', 'ينتظر الراتب'])[rand(0, 3)],
            'expiry_bucket' => (['today', '3_days', '7_days', '14_days', '30_days'])[rand(0, 4)],
            'current_end_date' => Carbon::now()->addDays(rand(-5, 30))->toDateString(),
            'new_start_date' => Carbon::now()->toDateString(),
            'new_end_date' => Carbon::now()->addMonths(rand(1, 6))->toDateString(),
            'renewal_amount_collected', 'collected_amount' => (string) rand(600, 2500),
            'renewal_payment_method', 'payment_method' => (['شبكة / بطاقة بنكية', 'كاش', 'تحويل بنكي', 'أونلاين / رابط دفع'])[rand(0, 3)],
            'renewed_package', 'package_name', 'current_package' => "باقة {$meta['sport']} - 3 أشهر",
            'coach' => (['كابتن أحمد', 'كابتن محمود', 'كابتن مصطفى', 'كابتن سارة'])[rand(0, 3)],
            'branch' => 'الفرع الرئيسي',
            'player_happiness_rating' => (string) rand(7, 10),
            'coach_evaluation_rating' => (string) rand(8, 10),
            'facility_cleanliness_rating' => (string) rand(8, 10),
            'nps_score' => (string) rand(8, 10),
            'can_winback_later' => 'نعم مع بداية الموسم الصيفي',
            default => match ($type) {
                'number' => (string) rand(100, 1000),
                'date' => Carbon::now()->subMonths(rand(1, 12))->toDateString(),
                'datetime' => Carbon::now()->addDays(rand(1, 10))->toDateTimeString(),
                'select', 'radio' => $this->pickOption($field->options),
                'checkbox', 'boolean' => 'نعم',
                'textarea' => 'ملاحظات تدريبية دورية منتظمة',
                default => 'بيانات مسجلة للأكاديمية',
            },
        };
    }

    private function pickOption(?string $rawOptions): ?string
    {
        if (empty($rawOptions)) {
            return null;
        }
        $decoded = json_decode($rawOptions, true);
        if (is_array($decoded) && !empty($decoded)) {
            $opt = $decoded[array_rand($decoded)];
            return is_array($opt) ? ($opt['value'] ?? $opt['label_ar'] ?? null) : (string) $opt;
        }
        return null;
    }
}
