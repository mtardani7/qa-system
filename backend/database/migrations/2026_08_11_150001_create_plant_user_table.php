<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('plant_user')) {
            Schema::create('plant_user', function (Blueprint $table): void {
                $table->foreignId('plant_id')->constrained('plants')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->primary(['plant_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('plant_user');
    }
};
