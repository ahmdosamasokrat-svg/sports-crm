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

        if (! Schema::hasColumn('leads', 'birth_date')) {
            Schema::table('leads', function (Blueprint $table): void {
                $table->date('birth_date')->nullable()->after('email');
            });
        }
    }

    public function down(): void
    {
        $this->verifyDatabase();

        if (Schema::hasColumn('leads', 'birth_date')) {
            Schema::table('leads', function (Blueprint $table): void {
                $table->dropColumn('birth_date');
            });
        }
    }
};
