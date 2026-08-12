<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('daily_reports', function (Blueprint $table): void { $table->string('status', 20)->default('draft')->after('remarks'); $table->index(['status', 'production_date']); });
    }

    public function down(): void { Schema::table('daily_reports', function (Blueprint $table): void { $table->dropIndex(['status', 'production_date']); $table->dropColumn('status'); }); }
};
