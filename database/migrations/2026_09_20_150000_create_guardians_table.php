<?php

declare(strict_types=1);

use App\Support\CrmDatabaseGuard;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function verifyDatabase(): void
    {
        CrmDatabaseGuard::ensureConnected();
    }

    public function up(): void
    {
        $this->verifyDatabase();

        if (! Schema::hasTable('guardians')) {
            Schema::create('guardians', function (Blueprint $table): void {
                $table->id();
                $table->string('name', 150)->index();
                $table->string('phone', 50)->nullable()->index();
                $table->string('secondary_phone', 50)->nullable();
                $table->string('email', 190)->nullable()->index();
                $table->string('relationship', 50)->nullable()->default('أب');
                $table->text('notes')->nullable();
                $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasColumn('leads', 'guardian_id')) {
            Schema::table('leads', function (Blueprint $table): void {
                $table->foreignId('guardian_id')
                    ->nullable()
                    ->after('parent_lead_id')
                    ->constrained('guardians')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        $this->verifyDatabase();

        if (Schema::hasColumn('leads', 'guardian_id')) {
            Schema::table('leads', function (Blueprint $table): void {
                $table->dropForeign(['guardian_id']);
                $table->dropColumn('guardian_id');
            });
        }

        Schema::dropIfExists('guardians');
    }
};
