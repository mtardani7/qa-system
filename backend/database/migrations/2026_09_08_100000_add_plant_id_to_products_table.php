<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->foreignId('plant_id')->nullable()->after('id')->constrained('plants')->nullOnDelete();
            $table->dropUnique('products_code_unique');
            $table->dropUnique('products_name_unique');
            $table->dropUnique('products_mm_number_unique');
            $table->unique(['plant_id', 'code'], 'products_plant_code_unique');
            $table->unique(['plant_id', 'name'], 'products_plant_name_unique');
            $table->unique(['plant_id', 'mm_number'], 'products_plant_mm_number_unique');
            $table->index(['plant_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropUnique('products_plant_code_unique');
            $table->dropUnique('products_plant_name_unique');
            $table->dropUnique('products_plant_mm_number_unique');
            $table->dropIndex(['plant_id', 'is_active']);
            $table->dropForeign(['plant_id']);
            $table->dropColumn('plant_id');
            $table->unique('code');
            $table->unique('name');
            $table->unique('mm_number', 'products_mm_number_unique');
        });
    }
};