<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('daily_reports', 'product_type')) {
            Schema::table('daily_reports', function (Blueprint $table): void {
                $table->string('product_type', 3)->nullable()->after('product_id');
            });
        }
        if (!Schema::hasColumn('daily_reports', 'finding_range_box')) {
            Schema::table('daily_reports', function (Blueprint $table): void {
                $table->string('finding_range_box', 100)->nullable()->after('quantity_defect');
            });
        }
        if (!Schema::hasColumn('daily_reports', 'finding_observation')) {
            Schema::table('daily_reports', function (Blueprint $table): void {
                $table->text('finding_observation')->nullable()->after('finding_range_box');
            });
        }
        if (!Schema::hasIndex('daily_reports', 'daily_reports_product_type_production_date_index')) {
            Schema::table('daily_reports', function (Blueprint $table): void {
                $table->index(['product_type', 'production_date']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('daily_reports', 'finding_observation')) Schema::table('daily_reports', fn (Blueprint $table) => $table->dropColumn('finding_observation'));
        if (Schema::hasColumn('daily_reports', 'finding_range_box')) Schema::table('daily_reports', fn (Blueprint $table) => $table->dropColumn('finding_range_box'));
        if (Schema::hasIndex('daily_reports', 'daily_reports_product_type_production_date_index')) Schema::table('daily_reports', fn (Blueprint $table) => $table->dropIndex('daily_reports_product_type_production_date_index'));
        if (Schema::hasColumn('daily_reports', 'product_type')) Schema::table('daily_reports', fn (Blueprint $table) => $table->dropColumn('product_type'));
    }
};
