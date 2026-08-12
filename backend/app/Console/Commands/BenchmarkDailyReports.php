<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Development-only load test for Daily Report query performance.
 *
 * Generates synthetic rows inside a transaction and rolls back at the end,
 * so it never modifies real data. Safe to re-run at any time.
 */
class BenchmarkDailyReports extends Command
{
    protected $signature = 'qms:benchmark-daily-reports {count=100000 : Number of synthetic rows to generate for the benchmark}';
    protected $description = 'Load-test Daily Report list/search queries with synthetic data (rolled back automatically, no real data is modified).';

    public function handle(): int
    {
        if (app()->environment('production')) {
            $this->error('Refusing to run the benchmark in the production environment.');
            return self::FAILURE;
        }

        if (DB::connection()->getDriverName() !== 'pgsql') {
            $this->error('This benchmark expects a PostgreSQL connection.');
            return self::FAILURE;
        }

        $count = (int) $this->argument('count');
        $plantId = (int) DB::table('plants')->value('id');
        $machineId = (int) DB::table('machines')->where('plant_id', $plantId)->value('id');
        $shiftId = (int) DB::table('shifts')->value('id');
        $productId = (int) DB::table('products')->value('id');
        $checkerId = (int) DB::table('qa_checkers')->value('id');
        $checker2Id = (int) DB::table('qa_checkers')->skip(1)->value('id') ?: $checkerId;

        if (!$plantId || !$machineId || !$shiftId || !$productId || !$checkerId) {
            $this->error('Missing reference master data (plant/machine/shift/product/qa-checker) required to build synthetic rows.');
            return self::FAILURE;
        }

        $this->info("Benchmarking with {$count} synthetic rows (rolled back afterwards, nothing is persisted)...");

        DB::beginTransaction();
        try {
            $baseline = (int) DB::table('daily_reports')->count();
            $this->line("Existing real rows before benchmark: {$baseline}");

            $this->insertSyntheticRows($count, $plantId, $machineId, $shiftId, $productId, $checkerId, $checker2Id);
            DB::statement('ANALYZE daily_reports');

            $this->line('');
            $this->info('--- Default paginated list query (plant scope + order by production_date desc, limit 50) ---');
            $this->explain($this->listQuerySql(), [$plantId]);

            $this->line('');
            $this->info('--- Server-side search query BEFORE trigram index (ILIKE on mm_number/po_number) ---');
            $this->explain($this->searchQuerySql(), ['%BENCH-TERM%', '%BENCH-TERM%']);

            $indexCreated = $this->tryCreateTrigramIndex();
            if ($indexCreated) {
                DB::statement('ANALYZE daily_reports');
                $this->line('');
                $this->info('--- Server-side search query AFTER trigram index ---');
                $this->explain($this->searchQuerySql(), ['%BENCH-TERM%', '%BENCH-TERM%']);
            } else {
                $this->warn('pg_trgm extension unavailable in this environment; skipped trigram index comparison.');
            }
        } finally {
            DB::rollBack();
            $this->line('');
            $this->info('Transaction rolled back: no synthetic data or index changes were kept.');
        }

        return self::SUCCESS;
    }

    private function insertSyntheticRows(int $count, int $plantId, int $machineId, int $shiftId, int $productId, int $checkerId, int $checker2Id): void
    {
        $chunkSize = 2000;
        $inserted = 0;
        $startDate = now()->subYears(2);
        $bar = $this->output->createProgressBar($count);

        while ($inserted < $count) {
            $rows = [];
            $batch = min($chunkSize, $count - $inserted);
            for ($i = 0; $i < $batch; $i++) {
                $n = $inserted + $i;
                $date = $startDate->copy()->addDays($n % 730);
                $isBenchMatch = $n % 5000 === 0;
                $rows[] = [
                    'plant_id' => $plantId,
                    'machine_id' => $machineId,
                    'shift_id' => $shiftId,
                    'product_id' => $productId,
                    'product_type' => $n % 2 === 0 ? 'FG' : 'WIP',
                    'checker_id' => $checkerId,
                    'checker_2_id' => $checker2Id,
                    'mm_number' => $isBenchMatch ? 'BENCH-TERM-'.$n : ('MM-SYN-'.$n),
                    'po_number' => $isBenchMatch ? 'PO-BENCH-TERM-'.$n : ('PO-SYN-'.$n),
                    'output_box' => 100,
                    'qty_per_box' => 20,
                    'output_pcs' => 2000,
                    'quantity_defect' => 0,
                    'production_date' => $date->toDateString(),
                    'status' => 'draft',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('daily_reports')->insert($rows);
            $inserted += $batch;
            $bar->advance($batch);
        }

        $bar->finish();
        $this->line('');
    }

    private function listQuerySql(): string
    {
        return 'SELECT id, plant_id, machine_id, shift_id, product_id, product_type, checker_id, checker_2_id, mm_number, po_number, output_box, qty_per_box, output_pcs, quantity_defect, finding_range_box, finding_observation, category, qa_checker_id, production_date, remarks, result, status, created_at
                FROM daily_reports
                WHERE plant_id = ?
                ORDER BY production_date DESC
                LIMIT 50';
    }

    private function searchQuerySql(): string
    {
        return 'SELECT id FROM daily_reports WHERE mm_number ILIKE ? OR po_number ILIKE ? LIMIT 50';
    }

    private function tryCreateTrigramIndex(): bool
    {
        try {
            DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');
            DB::statement('CREATE INDEX daily_reports_mm_number_trgm_idx ON daily_reports USING gin (mm_number gin_trgm_ops)');
            DB::statement('CREATE INDEX daily_reports_po_number_trgm_idx ON daily_reports USING gin (po_number gin_trgm_ops)');
            return true;
        } catch (\Throwable $exception) {
            $this->warn('Could not create trigram index: '.$exception->getMessage());
            return false;
        }
    }

    private function explain(string $sql, array $bindings): void
    {
        $plan = DB::select('EXPLAIN (ANALYZE, FORMAT TEXT) '.$sql, $bindings);
        foreach ($plan as $line) {
            $this->line((string) array_values((array) $line)[0]);
        }
    }
}
