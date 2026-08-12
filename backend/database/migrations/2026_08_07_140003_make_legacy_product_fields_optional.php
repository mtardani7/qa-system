<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void { $table->string('code', 50)->nullable()->change(); $table->string('name', 200)->nullable()->change(); });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void { $table->string('code', 50)->nullable(false)->change(); $table->string('name', 200)->nullable(false)->change(); });
    }
};
