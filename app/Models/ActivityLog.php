<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'user_name',
        'action',
        'module',
        'subject_type',
        'subject_id',
        'subject_label',
        'description',
        'properties',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForUser(Builder $query, int|string $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForModule(Builder $query, string $module): Builder
    {
        return $query->where('module', $module);
    }

    public function scopeForAction(Builder $query, string $action): Builder
    {
        return $query->where('action', $action);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (empty($term)) {
            return $query;
        }

        $term = trim($term);

        return $query->where(function (Builder $q) use ($term): void {
            $q->where('description', 'like', "%{$term}%")
                ->orWhere('subject_label', 'like', "%{$term}%")
                ->orWhere('user_name', 'like', "%{$term}%")
                ->orWhere('ip_address', 'like', "%{$term}%");
        });
    }

    public function scopeDateBetween(Builder $query, ?string $from, ?string $to): Builder
    {
        if (!empty($from)) {
            $query->whereDate('created_at', '>=', $from);
        }

        if (!empty($to)) {
            $query->whereDate('created_at', '<=', $to);
        }

        return $query;
    }

    public function localizedAction(): string
    {
        $isEn = app()->getLocale() === 'en';

        return match ($this->action) {
            'lead.created' => $isEn ? 'Create Lead' : 'إضافة عميل',
            'lead.updated' => $isEn ? 'Update Lead' : 'تعديل بيانات عميل',
            'lead.deleted' => $isEn ? 'Delete Lead' : 'حذف عميل',
            'lead.stage_transition' => $isEn ? 'Stage Transition' : 'تغيير مرحلة العميل',
            'lead.bulk_assign' => $isEn ? 'Bulk Assign' : 'إسناد جماعي للعملاء',
            'lead.exported' => $isEn ? 'Export Leads' : 'تصدير العملاء',
            'lead.trashed' => $isEn ? 'Move to Trash' : 'نقل إلى سلة المهملات',
            'lead.restored' => $isEn ? 'Restore Lead' : 'استعادة العميل',
            'lead.force_deleted' => $isEn ? 'Permanent Delete' : 'حذف نهائي للعميل',
            'lead.imported' => $isEn ? 'Import Leads' : 'استيراد عملاء',

            'user.created' => $isEn ? 'Create User' : 'إضافة مستخدم',
            'user.updated' => $isEn ? 'Update User' : 'تعديل مستخدم',
            'user.deleted' => $isEn ? 'Delete User' : 'حذف مستخدم',

            'group.created' => $isEn ? 'Create Role/Group' : 'إضافة مجموعة صلاحيات',
            'group.updated' => $isEn ? 'Update Role/Group' : 'تعديل مجموعة صلاحيات',
            'group.deleted' => $isEn ? 'Delete Role/Group' : 'حذف مجموعة صلاحيات',

            'settings.permissions_updated' => $isEn ? 'Update Permissions' : 'تحديث مصفوفة الصلاحيات',
            'voip.paired' => $isEn ? 'Pair VoIP' : 'اقتران السنترال',
            'voip.disconnected' => $isEn ? 'Disconnect VoIP' : 'فصل السنترال',

            'leaddocument.created' => $isEn ? 'Upload Document' : 'رفع مستند للعميل',
            'leaddocument.updated' => $isEn ? 'Update Document' : 'تعديل مستند',
            'leaddocument.deleted' => $isEn ? 'Delete Document' : 'حذف مستند',

            'calendarevent.created' => $isEn ? 'Create Meeting' : 'جدولة موعد/حدث',
            'calendarevent.updated' => $isEn ? 'Update Meeting' : 'تعديل موعد/حدث',
            'calendarevent.deleted' => $isEn ? 'Delete Meeting' : 'حذف موعد/حدث',

            'pipelinestagefield.created' => $isEn ? 'Create Custom Field' : 'إضافة حقل مخصص للمرحلة',
            'pipelinestagefield.updated' => $isEn ? 'Update Custom Field' : 'تعديل حقل مخصص',
            'pipelinestagefield.deleted' => $isEn ? 'Delete Custom Field' : 'حذف حقل مخصص',

            'notificationrule.created' => $isEn ? 'Create Notification Rule' : 'إنشاء قاعدة إشعار',
            'notificationrule.updated' => $isEn ? 'Update Notification Rule' : 'تعديل قاعدة إشعار',
            'notificationrule.deleted' => $isEn ? 'Delete Notification Rule' : 'حذف قاعدة إشعار',

            'technicalsupportticket.created' => $isEn ? 'Open Support Ticket' : 'فتح تذكرة دعم فني',
            'technicalsupportticket.updated' => $isEn ? 'Update Support Ticket' : 'تعديل تذكرة دعم فني',
            'technicalsupportticket.deleted' => $isEn ? 'Delete Support Ticket' : 'حذف تذكرة دعم فني',

            'technicalsupporttask.created' => $isEn ? 'Create Support Task' : 'إنشاء مهمة دعم فني',
            'technicalsupporttask.updated' => $isEn ? 'Update Support Task' : 'تعديل مهمة دعم فني',
            'technicalsupporttask.deleted' => $isEn ? 'Delete Support Task' : 'حذف مهمة دعم فني',

            'quotation.created' => $isEn ? 'Create Quotation' : 'إنشاء عرض سعر',
            'quotation.updated' => $isEn ? 'Update Quotation' : 'تعديل عرض سعر',
            'quotation.deleted' => $isEn ? 'Delete Quotation' : 'حذف عرض سعر',

            'campaign.created' => $isEn ? 'Create Campaign' : 'إنشاء حملة',
            'campaign.updated' => $isEn ? 'Update Campaign' : 'تعديل حملة',
            'campaign.deleted' => $isEn ? 'Delete Campaign' : 'حذف حملة',

            'pipelinestage.created' => $isEn ? 'Create Stage' : 'إضافة مرحلة',
            'pipelinestage.updated' => $isEn ? 'Update Stage' : 'تعديل مرحلة',
            'pipelinestage.deleted' => $isEn ? 'Delete Stage' : 'حذف مرحلة',

            'auth.login' => $isEn ? 'User Login' : 'تسجيل دخول',
            'auth.logout' => $isEn ? 'User Logout' : 'تسجيل خروج',
            'auth.failed' => $isEn ? 'Failed Login' : 'فشل تسجيل الدخول',

            default => ucwords(str_replace(['.', '_'], ' ', $this->action)),
        };
    }

    public static function fieldLabel(string $field): string
    {
        $isEn = app()->getLocale() === 'en';

        return match ($field) {
            'name' => $isEn ? 'Name' : 'الاسم',
            'first_name' => $isEn ? 'First Name' : 'الاسم الأول',
            'last_name' => $isEn ? 'Last Name' : 'اسم العائلة',
            'phone' => $isEn ? 'Phone' : 'رقم الهاتف',
            'email' => $isEn ? 'Email' : 'البريد الإلكتروني',
            'company_name' => $isEn ? 'Company Name' : 'اسم الشركة',
            'governorate' => $isEn ? 'Governorate' : 'المحافظة',
            'address' => $isEn ? 'Address' : 'العنوان',
            'activity' => $isEn ? 'Activity' : 'النشاط',
            'job_title' => $isEn ? 'Job Title' : 'المسمى الوظيفي',
            'source' => $isEn ? 'Source' : 'المصدر',
            'assigned_employee' => $isEn ? 'Assigned Employee' : 'الموظف المسؤول',
            'assigned_user' => $isEn ? 'Assigned User' : 'المستخدم المسؤول',
            'assigned_user_id' => $isEn ? 'Assigned User' : 'المستخدم المسؤول',
            'lead_status_id', 'status' => $isEn ? 'Status' : 'الحالة',
            'next_follow_up_at' => $isEn ? 'Next Follow-up Date' : 'تاريخ المتابعة القادمة',
            'notes' => $isEn ? 'Notes' : 'الملاحظات',
            'disinterest_reason' => $isEn ? 'Disinterest Reason' : 'سبب عدم الاهتمام',
            'solution_type' => $isEn ? 'Solution Type' : 'نوع الحل المطلوب',
            'users_count' => $isEn ? 'Users Count' : 'عدد المستخدمين',
            'branches_count' => $isEn ? 'Branches Count' : 'عدد الفروع',
            'lines_count' => $isEn ? 'Lines Count' : 'عدد الخطوط',
            'extensions' => $isEn ? 'Extensions' : 'التحويلات',
            'departments' => $isEn ? 'Departments' : 'الأقسام',
            'quotation_sent' => $isEn ? 'Quotation Sent' : 'تم إرسال عرض السعر',
            'quotation_file_path' => $isEn ? 'Quotation File' : 'ملف عرض السعر',
            'is_active' => $isEn ? 'Active Status' : 'حالة التفعيل',
            'username' => $isEn ? 'Username' : 'اسم المستخدم',
            'locale' => $isEn ? 'Language' : 'اللغة',
            'voip_extension' => $isEn ? 'VoIP Extension' : 'تحويلة السنترال',
            'pipeline_stage_access_mode' => $isEn ? 'Stage Access Mode' : 'صلاحية الوصول للمراحل',
            default => ucwords(str_replace('_', ' ', $field)),
        };
    }

    public function targetUrl(): ?string
    {
        if (empty($this->subject_id) && empty($this->properties['lead_id'])) {
            return null;
        }

        try {
            $type = $this->subject_type ? class_basename($this->subject_type) : '';

            if ($type === 'Lead') {
                return route('v2.leads.show', $this->subject_id);
            }

            if ($type === 'LeadDocument') {
                $leadId = $this->properties['lead_id'] ?? null;
                if ($leadId) {
                    return route('v2.leads.show', $leadId);
                }
            }

            if ($type === 'Quotation') {
                return route('v2.quotations.show', $this->subject_id);
            }

            if ($type === 'Campaign') {
                return route('v2.campaigns.show', $this->subject_id);
            }

            if ($type === 'User') {
                return route('v2.settings.users.edit', $this->subject_id);
            }

            if ($type === 'Group') {
                return route('v2.settings.groups.edit', $this->subject_id);
            }

            if ($type === 'CalendarEvent') {
                return route('v2.calendar.index');
            }

            if ($type === 'TechnicalSupportTicket') {
                return route('v2.technical-support.index');
            }

            if ($type === 'TechnicalSupportTask') {
                return route('v2.technical-support.tasks.index');
            }

            if ($type === 'NotificationRule') {
                return route('v2.settings.notifications.edit', $this->subject_id);
            }

            if ($type === 'PipelineStage') {
                return route('v2.settings.stages.index');
            }
        } catch (\Throwable) {
            return null;
        }

        return null;
    }
}

