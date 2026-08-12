<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('daily_reports', function (Blueprint $table): void {
            $table->foreignId('checker_id')->nullable()->after('product_id')->constrained('qa_checkers')->restrictOnDelete();
            $table->text('remarks')->nullable()->after('production_date');
            $table->index(['checker_id', 'production_date']);
            $table->index(['po_number', 'production_date']);
        });
        Schema::table('daily_reports', function (Blueprint $table): void { $table->dropForeign(['qa_checker_id']); $table->foreignId('qa_checker_id')->nullable()->change(); });
        Schema::create('daily_report_defects', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('daily_report_id')->constrained('daily_reports')->cascadeOnDelete();
            $table->foreignId('defect_id')->constrained('defects')->restrictOnDelete();
            $table->unsignedInteger('quantity');
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->unique(['daily_report_id', 'defect_id']);
            $table->index(['defect_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_report_defects');
        Schema::table('daily_reports', function (Blueprint $table): void { $table->dropForeign(['checker_id']); $table->dropIndex(['checker_id', 'production_date']); $table->dropIndex(['po_number', 'production_date']); $table->dropColumn(['checker_id', 'remarks']); });
    }
};
