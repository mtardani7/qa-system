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
    private bool $modernFormatWithoutDate = false;
    private array $modernPrevious = [];
    private int $rowNumber = 0;
    private bool $headerRead = false;

    public function __construct(private readonly DailyReportImport $import) {}

    public function startRow(): int { return 1; }

    public function chunkSize(): int { return 250; }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $line = ++$this->rowNumber;
            $values = array_values($row->toArray());
            if (!$this->headerRead) {
                $this->modernFormat = $this->isModernHeader($values);
                if ($this->isModernHeaderWithoutDate($values)) {
                    $this->modernFormat = true;
                    $this->modernFormatWithoutDate = true;
                    $values = $this->normalizeModernRow($values);
                }
                if ($this->modernFormat) {
                    $this->headerRead = true;
                    continue;
                }
                if (strtoupper(trim((string) ($values[0] ?? ''))) === 'TGL PRODUKSI') {
                    $this->headerRead = true;
                    continue;
                }
                if ($line < 9) continue;
                $this->headerRead = true;
            }
            if ($this->modernFormatWithoutDate) {
                $values = $this->normalizeModernRow($values);
            }
            if (!$this->modernFormat && $line < 9) continue;
            $this->processed++;

            try {
                $this->modernFormat ? $this->consumeModern($values, $line) : $this->consume($values, $line);
            } catch (\Throwable $exception) {
                $this->errors[] = ['row' => $line, 'message' => $exception->getMessage()];
                try {
                    while (DB::connection()->transactionLevel() > 0) DB::rollBack();
                    DB::connection()->reconnect();
                } catch (\Throwable) {
                    // The row error is already recorded; the next row gets a fresh connection when possible.
                }
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

    private function isModernHeader(array $row): bool
    {
        return strtoupper(trim((string) ($row[0] ?? ''))) === 'TGL PRODUKSI'
            && strtoupper(trim((string) ($row[3] ?? ''))) === 'TYPE PRODUK';
    }

    private function isModernHeaderWithoutDate(array $row): bool
    {
        return strtoupper(trim((string) ($row[0] ?? ''))) === 'SHIFT'
            && strtoupper(trim((string) ($row[2] ?? ''))) === 'TYPE PRODUK';
    }

    private function normalizeModernRow(array $row): array
    {
        array_unshift($row, $this->import->created_at?->toDateString() ?? now()->toDateString());
        return $row;
    }

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

            if ($defectName !== '') {
                $defect = $this->lookupDefect($defectName, '', $remarks);
                $this->current['defects'][] = [
                    'defect_id' => $defect->id,
                    'quantity' => $quantity > 0 ? $quantity : null,
                    'remarks' => $remarks !== '' ? $remarks : null,
                ];
            }
        }
    }

    private function consumeModern(array $row, int $line): void
    {
        if ($this->empty($row)) return;
        $rawMachine = trim((string) ($row[2] ?? ''));
        $rawMm = $this->number($row[4] ?? null);
        $rawPo = $this->number($row[6] ?? null);
        $rawDate = $row[0] ?? null;
        if ($this->current !== null && $rawDate !== null && trim((string) $rawDate) !== '') {
            $rowDate = $this->date($rawDate);
            if ($rowDate !== $this->current['production_date']) $this->flush();
        }
        if ($this->current !== null && ($rawMachine === '' || $rawMm === null || $rawPo === null)) {
            $this->consumeModernContinuation($row);
            return;
        }
        $mm = $this->number($row[4] ?? null);
        foreach ([0, 1, 2, 3, 18, 19] as $index) {
            if (($row[$index] ?? null) === null || trim((string) $row[$index]) === '') $row[$index] = $this->modernPrevious[$index] ?? null;
            elseif ($row[$index] !== null && trim((string) $row[$index]) !== '') $this->modernPrevious[$index] = $row[$index];
        }

        // Rows without their own MM number continue the currently open production row (extra defect/finding lines).
        if ($mm === null) {
            $this->consumeModernContinuation($row);
            return;
        }

        $machine = trim((string) ($row[2] ?? ''));
        if ($machine === '') throw new \RuntimeException('Production row is missing machine or MM number.');

        $this->flush();
        $productName = $this->excelText($row[5] ?? null);
        $qtyPerBox = (int) ($row[7] ?? 0);
        $defectName = trim((string) ($row[14] ?? ''));
        $defectDescription = trim((string) ($row[15] ?? ''));
        $defectCategory = $this->category($row[16] ?? null);
        $quantity = (int) ($row[13] ?? 0);
        $defects = [];
        if ($defectName !== '') {
            $defect = $this->lookupDefect($defectName, $defectCategory, $defectDescription);
            $defects[] = ['defect_id' => $defect->id, 'quantity' => $quantity > 0 ? $quantity : null, 'remarks' => $defectDescription !== '' ? $defectDescription : null];
        }
        $this->current = [
            'line' => $line,
            'production_date' => $this->date($row[0] ?? null),
            'shift' => $this->lookupShift($row[1] ?? null),
            'machine' => $this->lookupMachine($machine),
            'product' => $this->lookupProduct($mm, $productName, $qtyPerBox),
            'product_type' => trim((string) ($row[3] ?? 'FG')) ?: 'FG',
            'category' => $defectCategory ?: null,
            'po_number' => $this->number($row[6] ?? null) ?? ('IMPORT-'.$line),
            'output_box' => (int) ($row[7] ?? 0),
            'checker' => $this->lookupChecker($row[18] ?? null),
            'checker_2' => trim((string) ($row[19] ?? '')) !== '' ? $this->lookupChecker($row[19]) : null,
            'finding_range_box' => implode(' ', array_filter(array_map(fn ($value) => trim((string) $value), array_slice($row, 10, 3)))),
            'finding_observation' => null,
            'result' => trim((string) ($row[17] ?? '')) ?: null,
            'defects' => $defects,
        ];
    }

    // Adds a defect/finding line to the currently open production row; rows with no MM carry no new production data.
    private function consumeModernContinuation(array $row): void
    {
        if ($this->current === null) return;

        $quantity = (int) ($row[13] ?? 0);
        $defectName = trim((string) ($row[14] ?? ''));
        $defectDescription = trim((string) ($row[15] ?? ''));
        $defectCategory = $this->category($row[16] ?? null);
        if ($defectName !== '') {
            $defect = $this->lookupDefect($defectName, $defectCategory, $defectDescription);
            $this->current['defects'][] = ['defect_id' => $defect->id, 'quantity' => $quantity > 0 ? $quantity : null, 'remarks' => $defectDescription !== '' ? $defectDescription : null];
        }
    }

    private function flush(): void
    {
        if ($this->current === null) {
            return;
        }

        $current = $this->current;
        $this->current = null;
        $defects = [];
        foreach ($current['defects'] as $defect) {
            $key = (int) $defect['defect_id'];
            if (!isset($defects[$key])) {
                $defects[$key] = $defect;
                continue;
            }
            if ($defect['quantity'] !== null) {
                $defects[$key]['quantity'] = ($defects[$key]['quantity'] ?? 0) + $defect['quantity'];
            }
            if ($defect['remarks'] !== null && $defect['remarks'] !== '') {
                $defects[$key]['remarks'] = trim(($defects[$key]['remarks'] ?? '').' '.$defect['remarks']);
            }
        }
        $current['defects'] = array_values($defects);

        DB::transaction(function () use ($current): void {
            $attributes = [
                'plant_id' => $this->import->plant_id,
                'line_id' => $this->import->line_id,
                'machine_id' => $current['machine']->id,
                'shift_id' => $current['shift']->id,
                'product_id' => $current['product']->id,
                'mm_number' => $current['product']->mm_number,
                'checker_id' => $current['checker']?->id,
                'checker_2_id' => ($current['checker_2'] ?? null)?->id,
                'product_type' => $current['product_type'] ?? null,
                'category' => $current['category'] ?? null,
                'production_date' => $current['production_date'],
                'po_number' => $current['po_number'],
                'output_box' => $current['output_box'],
                'qty_per_box' => $current['product']->qty_per_box,
                'output_pcs' => $current['output_box'] * $current['product']->qty_per_box,
                'quantity_defect' => array_sum(array_map(fn ($defect) => $defect['quantity'] ?? 0, $current['defects'])),
                'finding_range_box' => $current['finding_range_box'] ?? null,
                'finding_observation' => $current['finding_observation'] ?? null,
                'result' => $current['result'] ?? null,
                'status' => 'draft',
                'created_by' => $this->import->created_by,
                'updated_by' => $this->import->created_by,
            ];

            $report = DailyReport::query()
                ->where('plant_id', $attributes['plant_id'])
                ->whereDate('production_date', $attributes['production_date'])
                ->where('shift_id', $attributes['shift_id'])
                ->where('machine_id', $attributes['machine_id'])
                ->where('product_id', $attributes['product_id'])
                ->where('po_number', $attributes['po_number'])
                ->oldest('id')
                ->first();

            if ($report) {
                $report->update($attributes);
                $report->defects()->delete();
            } else {
                $report = DailyReport::create($attributes);
            }

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
        $product = Product::withTrashed()->where('mm_number', $value)->first();
        if (!$product) $product = Product::withTrashed()->where('code', 'MM-'.$value)->first();
        if (!$product && $name !== '') $product = Product::withTrashed()->whereRaw('LOWER(name) = ?', [strtolower($name)])->first();
        if (!$product) {
            $product = new Product();
        }
        $code = $product->code ?: 'MM-'.$value;
        if (!$product->exists || $product->mm_number !== $value) {
            $codeOwner = Product::withTrashed()->where('code', $code)->where($product->getKeyName(), '!=', $product->getKey())->exists();
            if ($codeOwner) {
                $suffix = 2;
                do {
                    $candidate = $code.'-'.$suffix++;
                } while (Product::withTrashed()->where('code', $candidate)->exists());
                $code = $candidate;
            }
        }
        $safeName = $name !== '' ? $name : ($product->name ?: 'Imported '.$value);
        if (str_starts_with(trim($safeName), '=')) $safeName = 'Imported '.$value;
        $safeDescription = $name !== '' ? $name : $product->description;
        if (str_starts_with(trim((string) $safeDescription), '=')) $safeDescription = null;
        $product->fill(['code' => $code, 'name' => $safeName, 'mm_number' => $value, 'description' => $safeDescription, 'qty_per_box' => $qtyPerBox > 0 ? $qtyPerBox : ($product->qty_per_box ?: 1), 'is_active' => true]);
        $product->save();
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

    private function lookupChecker(mixed $value): ?QaChecker
    {
        $value = trim((string) $value);
        if ($value === '') return null;
        $key = strtolower($value);

        return $this->checkerCache[$key] ??= QaChecker::query()
            ->where('plant_id', $this->import->plant_id)
            ->where('is_active', true)
            ->whereRaw('LOWER(name) = ?', [$key])
            ->firstOrFail();
    }

    private function lookupDefect(string $value, string $category = '', string $description = ''): Defect
    {
        $key = strtolower($value);

        if (isset($this->defectCache[$key])) {
            return $this->defectCache[$key];
        }

        $defect = Defect::withTrashed()
            ->where('is_active', true)
            ->where(function ($query) use ($key): void {
                $query->whereRaw('LOWER(name) = ?', [$key])
                    ->orWhereRaw('LOWER(description) = ?', [$key])
                    ->orWhereRaw('LOWER(code) = ?', [$key]);
            })
            ->orderByRaw('CASE WHEN LOWER(name) = ? THEN 0 ELSE 1 END', [$key])
            ->first();

        if (!$defect) {
            $defect = Defect::withTrashed()->updateOrCreate(
                ['name' => $value],
                ['code' => 'IMPORT-'.strtoupper(substr(sha1($key), 0, 10)), 'description' => $description !== '' ? $description : $value, 'category' => $category !== '' ? $category : '-', 'is_active' => true],
            );
        } elseif ($defect->trashed()) {
            $defect->restore();
        }

        return $this->defectCache[$key] = $defect;
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

    private function category(mixed $value): string
    {
        $value = strtoupper(trim((string) $value));
        return in_array($value, ['CRITICAL', 'MAJOR', 'MINOR', 'UNACCEPTABLE', '-'], true) ? $value : '';
    }

    private function excelText(mixed $value): string
    {
        $value = trim(str_replace(["\xc2\xa0", "\xa0"], ' ', (string) $value));
        return str_starts_with($value, '=') ? '' : $value;
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
