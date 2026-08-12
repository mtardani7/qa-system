<?php

namespace App\Imports;

use App\Models\DailyReport;
use App\Models\DailyReportDefect;
use App\Models\DailyReportImport;
use App\Models\Defect;
use App\Models\Machine;
use App\Models\Product;
use App\Models\QaChecker;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithStartRow;

class DailyReportExcelImport implements ToCollection, WithChunkReading, WithStartRow
{
    private ?array $current = null;
    private array $errors = [];
    private int $processed = 0;
    private int $reports = 0;
    private array $machineCache = [];
    private array $productCache = [];
    private array $shiftCache = [];
    private array $checkerCache = [];
    private array $defectCache = [];
    private bool $modernFormat = false;
    private array $modernPrevious = [];

    public function __construct(private readonly DailyReportImport $import) {}

    public function startRow(): int { return 1; }

    public function chunkSize(): int { return 250; }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $offset => $row) {
            $line = $offset + 1;
            $values = array_values($row->toArray());
            if ($line === 1) {
                $this->modernFormat = strtoupper(trim((string) ($values[0] ?? ''))) === 'TGL PRODUKSI';
                continue;
            }
            if (!$this->modernFormat && $line < 9) continue;
            $this->processed++;

            try {
                $this->modernFormat ? $this->consumeModern($values, $line) : $this->consume($values, $line);
            } catch (\Throwable $exception) {
                $this->errors[] = ['row' => $line, 'message' => $exception->getMessage()];
                $this->flush();
            }
        }

        $this->syncProgress();
    }

    public function finish(): void
    {
        $this->flush();
        $this->import->update([
            'status' => empty($this->errors) ? 'completed' : 'completed_with_errors',
            'processed_rows' => $this->processed,
            'imported_reports' => $this->reports,
            'failed_rows' => count($this->errors),
            'errors' => array_slice($this->errors, 0, 500),
        ]);
    }

    public function errors(): array { return $this->errors; }

    public function processed(): int { return $this->processed; }

    public function reports(): int { return $this->reports; }

    private function consume(array $row, int $line): void
    {
        $date = $this->date($row[0] ?? null);
        $machine = trim((string) ($row[2] ?? ''));
        $mm = $this->number($row[4] ?? null);
        $po = $this->number($row[6] ?? null);
        $hasProduction = $machine !== '' && $mm !== null && ((int) ($row[9] ?? 0) > 0 || $po !== null);

        if ($hasProduction) {
            $this->flush();
            $this->current = [
                'line' => $line,
                'production_date' => $date,
                'shift' => $this->lookupShift($row[1] ?? null),
                'machine' => $this->lookupMachine($machine),
                'product' => $this->lookupProduct($mm),
                'po_number' => $po ?? ('IMPORT-'.$line),
                'output_box' => (int) ($row[9] ?? 0),
                'checker' => $this->lookupChecker($row[18] ?? null),
                'defects' => [],
            ];
        } elseif ($machine === '' && $this->current === null) {
            if ($this->empty($row)) {
                return;
            }
            throw new \RuntimeException('Continuation row has no preceding production row.');
        } elseif ($machine !== '' && $this->current === null) {
            throw new \RuntimeException('Production row is missing a valid product or output.');
        }

        if ($this->current !== null) {
            $quantity = (int) ($row[13] ?? 0);
            $defectName = trim((string) ($row[14] ?? ''));
            $remarks = trim((string) ($row[15] ?? ''));

            if ($quantity > 0) {
                $defect = $this->lookupDefect($defectName !== '' ? $defectName : $remarks);
                $this->current['defects'][] = [
                    'defect_id' => $defect->id,
                    'quantity' => $quantity,
                    'remarks' => $remarks !== '' ? $remarks : null,
                ];
            }
        }
    }

    private function consumeModern(array $row, int $line): void
    {
        if ($this->empty($row)) return;
        foreach ([0, 1, 2, 3, 18, 19] as $index) {
            if (($row[$index] ?? null) === null || trim((string) $row[$index]) === '') $row[$index] = $this->modernPrevious[$index] ?? null;
            elseif ($row[$index] !== null && trim((string) $row[$index]) !== '') $this->modernPrevious[$index] = $row[$index];
        }
        $machine = trim((string) ($row[2] ?? ''));
        $mm = $this->number($row[4] ?? null);
        if ($machine === '' || $mm === null) throw new \RuntimeException('Production row is missing machine or MM number.');

        $this->flush();
        $productName = trim((string) ($row[5] ?? ''));
        $qtyPerBox = (int) ($row[7] ?? 0);
        $defectName = trim((string) ($row[15] ?? ''));
        $quantity = (int) ($row[13] ?? 0);
        $defects = [];
        if ($quantity > 0 && $defectName !== '') {
            $defect = $this->lookupDefect($defectName);
            $defects[] = ['defect_id' => $defect->id, 'quantity' => $quantity, 'remarks' => trim((string) ($row[14] ?? '')) ?: null];
        }
        $this->current = [
            'line' => $line,
            'production_date' => $this->date($row[0] ?? null),
            'shift' => $this->lookupShift($row[1] ?? null),
            'machine' => $this->lookupMachine($machine),
            'product' => $this->lookupProduct($mm, $productName, $qtyPerBox),
            'product_type' => trim((string) ($row[3] ?? 'FG')) ?: 'FG',
            'po_number' => $this->number($row[6] ?? null) ?? ('IMPORT-'.$line),
            'output_box' => (int) ($row[9] ?? 0),
            'checker' => $this->lookupChecker($row[18] ?? null),
            'checker_2' => trim((string) ($row[19] ?? '')) !== '' ? $this->lookupChecker($row[19]) : null,
            'finding_range_box' => implode(' ', array_filter(array_map(fn ($value) => trim((string) $value), array_slice($row, 10, 3)))),
            'finding_observation' => trim((string) ($row[14] ?? '')) ?: null,
            'result' => trim((string) ($row[17] ?? '')) ?: null,
            'defects' => $defects,
        ];
    }

    private function flush(): void
    {
        if ($this->current === null) {
            return;
        }

        $current = $this->current;
        $this->current = null;

        DB::transaction(function () use ($current): void {
            $report = DailyReport::create([
                'plant_id' => $this->import->plant_id,
                'line_id' => $this->import->line_id,
                'machine_id' => $current['machine']->id,
                'shift_id' => $current['shift']->id,
                'product_id' => $current['product']->id,
                'mm_number' => $current['product']->mm_number,
                'checker_id' => $current['checker']->id,
                'checker_2_id' => ($current['checker_2'] ?? null)?->id,
                'product_type' => $current['product_type'] ?? null,
                'production_date' => $current['production_date'],
                'po_number' => $current['po_number'],
                'output_box' => $current['output_box'],
                'qty_per_box' => $current['product']->qty_per_box,
                'output_pcs' => $current['output_box'] * $current['product']->qty_per_box,
                'quantity_defect' => array_sum(array_column($current['defects'], 'quantity')),
                'finding_range_box' => $current['finding_range_box'] ?? null,
                'finding_observation' => $current['finding_observation'] ?? null,
                'result' => $current['result'] ?? null,
                'status' => 'draft',
                'created_by' => $this->import->created_by,
                'updated_by' => $this->import->created_by,
            ]);

            foreach ($current['defects'] as $defect) {
                DailyReportDefect::create([
                    'daily_report_id' => $report->id,
                    'defect_id' => $defect['defect_id'],
                    'quantity' => $defect['quantity'],
                    'remarks' => $defect['remarks'],
                ]);
            }
        });

        $this->reports++;
    }

    private function lookupMachine(string $value): Machine
    {
        $key = strtolower($value);

        return $this->machineCache[$key] ??= Machine::query()
            ->where('plant_id', $this->import->plant_id)
            ->when($this->import->line_id, fn ($query, $lineId) => $query->where('line_id', $lineId))
            ->where(fn ($query) => $query
                ->whereRaw('LOWER(name) = ?', [$key])
                ->orWhereRaw('LOWER(code) = ?', [$key])
                ->orWhere('machine_number', $value))
            ->firstOrFail();
    }

    private function lookupProduct(string $value, string $name = '', int $qtyPerBox = 0): Product
    {
        if (isset($this->productCache[$value])) return $this->productCache[$value];
        $product = Product::withTrashed()->where('mm_number', $value)->where('is_active', true)->first();
        if (!$product) {
            $product = Product::withTrashed()->updateOrCreate(
                ['mm_number' => $value],
                ['code' => 'MM-'.$value, 'name' => $name !== '' ? $name : 'Imported '.$value, 'description' => $name !== '' ? $name : null, 'qty_per_box' => $qtyPerBox > 0 ? $qtyPerBox : 1, 'is_active' => true],
            );
        }
        if ($product->trashed()) $product->restore();
        return $this->productCache[$value] = $product;
    }

    private function lookupShift(mixed $value): Shift
    {
        $value = $this->number($value);

        return $this->shiftCache[$value] ??= Shift::query()
            ->where('is_active', true)
            ->where(fn ($query) => $query
                ->where('code', (string) $value)
                ->orWhere('code', 'S'.(string) $value)
                ->orWhere('name', 'like', '%'.(string) $value.'%'))
            ->firstOrFail();
    }

    private function lookupChecker(mixed $value): QaChecker
    {
        $value = trim((string) $value);
        $key = strtolower($value);

        return $this->checkerCache[$key] ??= QaChecker::query()
            ->where('plant_id', $this->import->plant_id)
            ->where('is_active', true)
            ->whereRaw('LOWER(name) = ?', [$key])
            ->firstOrFail();
    }

    private function lookupDefect(string $value): Defect
    {
        $key = strtolower($value);

        return $this->defectCache[$key] ??= Defect::query()
            ->where('is_active', true)
            ->whereRaw('LOWER(name) = ?', [$key])
            ->firstOrFail();
    }

    private function date(mixed $value): string
    {
        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->toDateString();
        }

        if (is_numeric($value)) {
            return Carbon::create(1899, 12, 30)->addDays((int) $value)->toDateString();
        }

        $value = str_ireplace(['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'], ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'], (string) $value);
        return Carbon::parse($value)->toDateString();
    }

    private function number(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return preg_replace('/\.0+$/', '', (string) $value);
    }

    private function empty(array $row): bool
    {
        return count(array_filter($row, fn ($value) => $value !== null && $value !== '')) === 0;
    }

    private function syncProgress(): void
    {
        $this->import->update([
            'processed_rows' => $this->processed,
            'imported_reports' => $this->reports,
            'failed_rows' => count($this->errors),
        ]);
    }
}
