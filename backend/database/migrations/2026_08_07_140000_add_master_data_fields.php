<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('lines', function (Blueprint $table): void { $table->text('description')->nullable()->after('name'); });
        Schema::table('machines', function (Blueprint $table): void { $table->string('machine_number', 50)->nullable()->after('name'); $table->text('description')->nullable()->after('machine_number'); });
        Schema::table('products', function (Blueprint $table): void { $table->text('description')->nullable()->after('mm_number'); $table->string('category', 100)->nullable()->after('qty_per_box'); });
        Schema::table('defects', function (Blueprint $table): void { $table->text('description')->nullable()->after('category'); });
        Schema::table('products', function (Blueprint $table): void { $table->unique('mm_number', 'products_mm_number_unique'); $table->index(['category', 'is_active']); });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void { $table->dropUnique('products_mm_number_unique'); $table->dropIndex(['category', 'is_active']); });
        Schema::table('defects', function (Blueprint $table): void { $table->dropColumn('description'); });
        Schema::table('products', function (Blueprint $table): void { $table->dropColumn(['description', 'category']); });
        Schema::table('machines', function (Blueprint $table): void { $table->dropColumn(['machine_number', 'description']); });
        Schema::table('lines', function (Blueprint $table): void { $table->dropColumn('description'); });
    }
};
