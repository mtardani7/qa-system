<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('daily_reports', 'category')) {
            Schema::table('daily_reports', function (Blueprint $table): void {
                $table->string('category', 20)->nullable()->after('finding_observation');
                $table->index(['category', 'production_date']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('daily_reports', 'category')) {
            Schema::table('daily_reports', function (Blueprint $table): void {
                $table->dropIndex(['category', 'production_date']);
                $table->dropColumn('category');
            });
        }
    }
};
