<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('daily_report_audits', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('daily_report_id')->nullable()->constrained('daily_reports')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 40);
            $table->ipAddress('ip_address')->nullable();
            $table->json('old_value')->nullable();
            $table->json('new_value')->nullable();
            $table->timestamps();
            $table->index(['daily_report_id', 'created_at']);
            $table->index(['user_id', 'action', 'created_at']);
        });
    }

    public function down(): void { Schema::dropIfExists('daily_report_audits'); }
};
