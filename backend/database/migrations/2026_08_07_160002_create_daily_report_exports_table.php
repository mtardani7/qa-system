<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('daily_report_exports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('queued');
            $table->json('filters')->nullable();
            $table->string('file_path')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
            $table->index(['created_by', 'status', 'created_at']);
        });
    }

    public function down(): void { Schema::dropIfExists('daily_report_exports'); }
};
