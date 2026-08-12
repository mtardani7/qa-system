<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('shifts', function (Blueprint $table): void { $table->dropForeign(['plant_id']); });
        Schema::table('shifts', function (Blueprint $table): void { $table->foreignId('plant_id')->nullable()->change(); $table->foreign('plant_id')->references('id')->on('plants')->nullOnDelete(); });
    }

    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table): void { $table->dropForeign(['plant_id']); });
        Schema::table('shifts', function (Blueprint $table): void { $table->foreignId('plant_id')->nullable(false)->change(); $table->foreign('plant_id')->references('id')->on('plants')->cascadeOnDelete(); });
    }
};
