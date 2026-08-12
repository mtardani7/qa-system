<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'is_qa_checker')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->boolean('is_qa_checker')->default(false)->after('email');
                $table->index(['is_qa_checker', 'is_active']);
            });
            $qaUserIds = DB::table('model_has_roles')
                ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->whereIn('roles.name', ['QA Staff', 'QA Supervisor', 'QA Manager'])
                ->where('model_has_roles.model_type', 'App\\Models\\User')
                ->pluck('model_has_roles.model_id');
            DB::table('users')->whereIn('id', $qaUserIds)->update(['is_qa_checker' => true]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'is_qa_checker')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropIndex(['is_qa_checker', 'is_active']);
                $table->dropColumn('is_qa_checker');
            });
        }
    }
};
