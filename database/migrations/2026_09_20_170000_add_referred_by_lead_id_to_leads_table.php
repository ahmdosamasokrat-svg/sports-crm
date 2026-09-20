<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table): void {
            $table->foreignId('referred_by_lead_id')
                ->nullable()
                ->after('parent_lead_id')
                ->constrained('leads')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('referred_by_lead_id');
        });
    }
};
