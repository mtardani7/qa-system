<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('daily_reports', function (Blueprint $table): void {
            $table->index(['production_date', 'plant_id', 'shift_id', 'machine_id'], 'daily_reports_dashboard_scope_index');
            $table->index(['production_date', 'defect_id'], 'daily_reports_dashboard_defect_index');
            $table->index(['production_date', 'product_id'], 'daily_reports_dashboard_product_index');
        });
    }

    public function down(): void
    {
        Schema::table('daily_reports', function (Blueprint $table): void {
            $table->dropIndex('daily_reports_dashboard_scope_index');
            $table->dropIndex('daily_reports_dashboard_defect_index');
            $table->dropIndex('daily_reports_dashboard_product_index');
        });
    }
};
