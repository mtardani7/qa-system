<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table): void {
            $table->id(); $table->string('code', 30)->unique(); $table->string('name', 150); $table->text('address')->nullable(); $table->string('logo_path')->nullable(); $table->string('timezone', 64)->default('UTC'); $table->boolean('is_active')->default(true); $table->timestamps(); $table->softDeletes();
        });
        Schema::table('plants', function (Blueprint $table): void { $table->foreignId('company_id')->nullable()->after('id')->constrained()->nullOnDelete(); $table->index(['company_id', 'is_active']); });
        Schema::create('departments', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->constrained()->cascadeOnDelete(); $table->foreignId('plant_id')->nullable()->constrained()->nullOnDelete(); $table->string('code', 30); $table->string('name', 150); $table->boolean('is_active')->default(true); $table->timestamps(); $table->softDeletes(); $table->unique(['company_id', 'code']); $table->index(['plant_id', 'is_active']);
        });
        Schema::create('holidays', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->constrained()->cascadeOnDelete(); $table->foreignId('plant_id')->nullable()->constrained()->nullOnDelete(); $table->date('holiday_date'); $table->string('name', 150); $table->boolean('is_working_day')->default(false); $table->timestamps(); $table->softDeletes(); $table->unique(['company_id', 'plant_id', 'holiday_date']); $table->index(['holiday_date', 'is_working_day']);
        });
        Schema::create('system_settings', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->nullable()->constrained()->cascadeOnDelete(); $table->string('key', 100); $table->text('value')->nullable(); $table->string('type', 20)->default('string'); $table->timestamps(); $table->unique(['company_id', 'key']);
        });
        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->id(); $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); $table->nullableMorphs('auditable'); $table->string('action', 40); $table->ipAddress('ip_address')->nullable(); $table->json('old_values')->nullable(); $table->json('new_values')->nullable(); $table->timestamps(); $table->index(['action', 'created_at']);
        });
        Schema::create('user_activities', function (Blueprint $table): void {
            $table->id(); $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); $table->string('event', 80); $table->string('route')->nullable(); $table->string('method', 10)->nullable(); $table->ipAddress('ip_address')->nullable(); $table->text('user_agent')->nullable(); $table->json('metadata')->nullable(); $table->timestamp('created_at')->useCurrent(); $table->index(['user_id', 'created_at']); $table->index(['event', 'created_at']);
        });
        Schema::create('notifications', function (Blueprint $table): void { $table->uuid('id')->primary(); $table->string('type'); $table->morphs('notifiable'); $table->text('data'); $table->timestamp('read_at')->nullable(); $table->timestamps(); $table->index(['notifiable_type', 'notifiable_id', 'read_at']); });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications'); Schema::dropIfExists('user_activities'); Schema::dropIfExists('audit_logs'); Schema::dropIfExists('system_settings'); Schema::dropIfExists('holidays'); Schema::dropIfExists('departments'); Schema::table('plants', fn (Blueprint $table) => $table->dropConstrainedForeignId('company_id')); Schema::dropIfExists('companies');
    }
};
