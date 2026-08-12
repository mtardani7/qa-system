<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Evidenced by qms:benchmark-daily-reports: ILIKE '%term%' search on
 * mm_number/po_number falls back to a sequential scan as the table grows
 * (35ms / 2566 buffer reads at 20k synthetic rows). A trigram GIN index
 * turns this into a bitmap index scan (0.2ms / 38 buffer reads).
 */
return new class extends Migration {
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') return;

        try {
            DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');
        } catch (\Throwable) {
            return; // Managed DB without extension privileges: skip gracefully.
        }

        if (!Schema::hasIndex('daily_reports', 'daily_reports_mm_number_trgm_idx')) {
            DB::statement('CREATE INDEX daily_reports_mm_number_trgm_idx ON daily_reports USING gin (mm_number gin_trgm_ops)');
        }
        if (!Schema::hasIndex('daily_reports', 'daily_reports_po_number_trgm_idx')) {
            DB::statement('CREATE INDEX daily_reports_po_number_trgm_idx ON daily_reports USING gin (po_number gin_trgm_ops)');
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') return;
        DB::statement('DROP INDEX IF EXISTS daily_reports_mm_number_trgm_idx');
        DB::statement('DROP INDEX IF EXISTS daily_reports_po_number_trgm_idx');
    }
};
