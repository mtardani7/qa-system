<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('qa_checkers', 'position')) {
            Schema::table('qa_checkers', function (Blueprint $table): void {
                $table->string('position', 100)->default('')->after('name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('qa_checkers', 'position')) {
            Schema::table('qa_checkers', function (Blueprint $table): void {
                $table->dropColumn('position');
            });
        }
    }
};
