<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->string('mm_number', 100)->default('UNASSIGNED')->after('name');
            $table->unsignedInteger('qty_per_box')->default(1)->after('mm_number');
        });

        Schema::table('machines', function (Blueprint $table): void {
            $table->foreignId('line_id')->nullable()->after('plant_id')->constrained('lines')->nullOnDelete();
            $table->index(['line_id', 'is_active']);
        });

        Schema::table('daily_reports', function (Blueprint $table): void {
            $table->foreignId('line_id')->nullable()->after('plant_id')->constrained('lines')->restrictOnDelete();
            $table->index(['line_id', 'production_date']);
        });
    }

    public function down(): void
    {
        Schema::table('daily_reports', function (Blueprint $table): void { $table->dropConstrainedForeignId('line_id'); });
        Schema::table('machines', function (Blueprint $table): void { $table->dropConstrainedForeignId('line_id'); });
        Schema::table('products', function (Blueprint $table): void { $table->dropColumn(['mm_number', 'qty_per_box']); });
    }
};
