<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('plant_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30);
            $table->string('name', 100);
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['plant_id', 'code']);
            $table->index(['plant_id', 'is_active']);
        });
    }

    public function down(): void { Schema::dropIfExists('shifts'); }
};
