<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('qa_checkers', function (Blueprint $table): void {
            $table->id();
            $table->string('employee_number', 50)->unique();
            $table->string('name', 150);
            $table->string('position', 100);
            $table->foreignId('plant_id')->constrained('plants')->restrictOnDelete();
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['plant_id', 'is_active', 'name']);
        });
    }

    public function down(): void { Schema::dropIfExists('qa_checkers'); }
};
