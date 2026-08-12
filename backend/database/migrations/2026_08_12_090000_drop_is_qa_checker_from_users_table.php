<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'is_qa_checker')) {
                $table->dropColumn('is_qa_checker');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (!Schema::hasColumn('users', 'is_qa_checker')) {
                $table->boolean('is_qa_checker')->default(false)->after('email');
            }
        });
    }
};
