<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('daily_report_imports', function (Blueprint $table): void {
            $table->string('status', 30)->default('queued')->change();
        });
    }

    public function down(): void
    {
        Schema::table('daily_report_imports', function (Blueprint $table): void {
            $table->string('status', 20)->default('queued')->change();
        });
    }
};
