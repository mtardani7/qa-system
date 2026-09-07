<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('daily_reports', function (Blueprint $table): void {
            $table->jsonb('master_snapshot')->nullable();
        });

        DB::table('daily_reports')->where('status', '!=', 'locked')->update(['status' => 'draft']);
    }

    public function down(): void
    {
        Schema::table('daily_reports', function (Blueprint $table): void {
            $table->dropColumn('master_snapshot');
        });
    }
};