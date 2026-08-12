<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (!Schema::hasColumn('users', 'employee_number')) {
                $table->string('employee_number', 50)->nullable()->unique()->after('name');
            }
            if (!Schema::hasColumn('users', 'is_qa_checker')) {
                $table->boolean('is_qa_checker')->default(false)->after('email');
            }
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('is_qa_checker');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            foreach (['employee_number', 'is_qa_checker', 'is_active'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
