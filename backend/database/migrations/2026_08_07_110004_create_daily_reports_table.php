<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('daily_reports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('plant_id')->constrained()->restrictOnDelete();
            $table->foreignId('machine_id')->constrained()->restrictOnDelete();
            $table->foreignId('shift_id')->constrained()->restrictOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->string('mm_number', 100);
            $table->string('po_number', 100);
            $table->unsignedInteger('output_box');
            $table->unsignedInteger('qty_per_box');
            $table->unsignedInteger('output_pcs');
            $table->foreignId('defect_id')->nullable()->constrained()->restrictOnDelete();
            $table->unsignedInteger('quantity_defect')->default(0);
            $table->foreignId('qa_checker_id')->constrained('users')->restrictOnDelete();
            $table->date('production_date');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['plant_id', 'production_date', 'id']);
            $table->index(['machine_id', 'shift_id', 'production_date']);
            $table->index(['product_id', 'production_date']);
            $table->index(['defect_id', 'production_date']);
            $table->index(['mm_number', 'po_number']);
        });
    }

    public function down(): void { Schema::dropIfExists('daily_reports'); }
};
