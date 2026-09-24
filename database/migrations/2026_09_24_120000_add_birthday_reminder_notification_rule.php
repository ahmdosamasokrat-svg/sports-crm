<?php

declare(strict_types=1);

use App\Models\NotificationRule;
use App\Support\CrmDatabaseGuard;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        CrmDatabaseGuard::ensureConnected();

        if (! Schema::hasTable('notification_rules')) {
            return;
        }

        $now = now();
        $exists = DB::table('notification_rules')
            ->where('event_key', 'birthday.reminder')
            ->exists();

        if (! $exists) {
            $ruleId = DB::table('notification_rules')->insertGetId([
                'name_ar' => 'تذكير بعيد ميلاد اللاعب',
                'name_en' => 'Athlete Birthday Reminder',
                'event_key' => 'birthday.reminder',
                'enabled' => true,
                'trigger_offset_minutes' => 0,
                'escalation_after_minutes' => null,
                'priority' => 'important',
                'conditions' => null,
                'created_by_user_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Add internal notification channels only (strictly no sms/whatsapp)
            if (Schema::hasTable('notification_rule_channels')) {
                DB::table('notification_rule_channels')->insertOrIgnore([
                    [
                        'notification_rule_id' => $ruleId,
                        'channel' => 'database',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ],
                    [
                        'notification_rule_id' => $ruleId,
                        'channel' => 'push',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ],
                ]);
            }

            // Add assigned_user as default recipient
            if (Schema::hasTable('notification_rule_recipients')) {
                DB::table('notification_rule_recipients')->insertOrIgnore([
                    [
                        'notification_rule_id' => $ruleId,
                        'recipient_type' => 'assigned_user',
                        'recipient_id' => 0,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ],
                ]);
            }
        }
    }

    public function down(): void
    {
        CrmDatabaseGuard::ensureConnected();

        if (Schema::hasTable('notification_rules')) {
            $ruleId = DB::table('notification_rules')
                ->where('event_key', 'birthday.reminder')
                ->value('id');

            if ($ruleId) {
                if (Schema::hasTable('notification_rule_channels')) {
                    DB::table('notification_rule_channels')->where('notification_rule_id', $ruleId)->delete();
                }
                if (Schema::hasTable('notification_rule_recipients')) {
                    DB::table('notification_rule_recipients')->where('notification_rule_id', $ruleId)->delete();
                }
                DB::table('notification_rules')->where('id', $ruleId)->delete();
            }
        }
    }
};
