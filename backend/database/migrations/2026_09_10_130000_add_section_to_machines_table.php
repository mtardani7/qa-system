<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('machines', function (Blueprint $table): void {
            $table->string('section', 40)->nullable()->after('name');
            $table->index('section');
        });
    }

    public function down(): void
    {
        Schema::table('machines', function (Blueprint $table): void {
            $table->dropIndex(['section']);
            $table->dropColumn('section');
        });
    }
};
