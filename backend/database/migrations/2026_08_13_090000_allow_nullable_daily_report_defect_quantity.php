<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('daily_report_defects', function (Blueprint $table): void {
            $table->unsignedInteger('quantity')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('daily_report_defects', function (Blueprint $table): void {
            $table->unsignedInteger('quantity')->nullable(false)->change();
        });
    }
};