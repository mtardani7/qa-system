<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('daily_reports', 'checker_2_id')) {
            Schema::table('daily_reports', function (Blueprint $table): void {
                $table->foreignId('checker_2_id')->nullable()->after('checker_id')->constrained('qa_checkers')->nullOnDelete();
                $table->index(['checker_2_id', 'production_date']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('daily_reports', 'checker_2_id')) {
            Schema::table('daily_reports', function (Blueprint $table): void {
                $table->dropForeign(['checker_2_id']);
                $table->dropIndex(['checker_2_id', 'production_date']);
                $table->dropColumn('checker_2_id');
            });
        }
    }
};
