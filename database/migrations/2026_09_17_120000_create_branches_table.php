<?php

declare(strict_types=1);

use App\Support\CrmDatabaseGuard;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        CrmDatabaseGuard::ensureConnected();

        if (! Schema::hasTable('branches')) {
            Schema::create('branches', function (Blueprint $table): void {
                $table->id();
                $table->string('name_ar', 150);
                $table->string('name_en', 150)->nullable();
                $table->string('code', 50)->unique();
                $table->string('phone', 50)->nullable();
                $table->string('address', 255)->nullable();
                $table->boolean('is_active')->default(true);
                $table->softDeletes();
                $table->timestamps();

                $table->index(['is_active', 'name_ar'], 'branches_active_name_ar_idx');
            });
        }
    }

    public function down(): void
    {
        CrmDatabaseGuard::ensureConnected();

        Schema::dropIfExists('branches');
    }
};
