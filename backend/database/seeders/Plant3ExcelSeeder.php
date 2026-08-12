<?php

namespace Database\Seeders;

use App\Imports\DailyReportExcelImport;
use App\Models\DailyReportImport;
use App\Models\Defect;
use App\Models\Line;
use App\Models\Machine;
use App\Models\Plant;
use App\Models\Product;
use App\Models\QaChecker;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Plant3ExcelSeeder extends Seeder
{
    private const DEFAULT_FILE = 'D:/12. Daily Report QA Desember 2025.xlsx';

    public function run(): void
    {
        $filePath = (string) env('QA_DUMMY_EXCEL_PATH', self::DEFAULT_FILE);

        if (! is_file($filePath)) {
            throw new \RuntimeException("Dummy Excel file not found: {$filePath}. Set QA_DUMMY_EXCEL_PATH to the workbook path.");
        }

        $admin = User::query()->where('email', 'superadmin@example.com')->first()
            ?? User::query()->where('email', 'test@example.com')->firstOrFail();
        $plant = Plant::query()->firstOrCreate(
            ['code' => 'P3'],
            ['name' => 'Plant 3', 'description' => 'Dummy data imported from Daily QA Report', 'is_active' => true],
        );
        $line = Line::query()->firstOrCreate(
            ['plant_id' => $plant->id, 'code' => 'L01'],
            ['name' => 'Imported Production Line', 'description' => 'Line for Plant 3 workbook data', 'is_active' => true],
        );

        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filePath);

        DB::transaction(function () use ($spreadsheet, $plant, $line): void {
            $this->seedProducts($spreadsheet->getSheetByName('MASTER'));
            $this->seedDailyReportMasters($spreadsheet->getSheetByName('DAILY REPORT'), $plant, $line);
        });

        $fileName = basename($filePath);
        $existingImport = DailyReportImport::query()
            ->where('plant_id', $plant->id)
            ->where('line_id', $line->id)
            ->where('file_name', $fileName)
            ->whereIn('status', ['completed', 'completed_with_errors'])
            ->exists();

        if ($existingImport) {
            $this->command?->warn("Import already completed for {$fileName}; transaction rows were not duplicated.");
            return;
        }

        $importRecord = DailyReportImport::query()->create([
            'created_by' => $admin->id,
            'plant_id' => $plant->id,
            'line_id' => $line->id,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'status' => 'processing',
        ]);
        $dailyImport = new DailyReportExcelImport($importRecord);

        $this->importDailyReportSheet($dailyImport, $spreadsheet->getSheetByName('DAILY REPORT'));
        $dailyImport->finish();

        $this->command?->info("Plant 3 dummy import completed: {$dailyImport->reports()} reports, {$dailyImport->processed()} rows processed.");
    }

    private function seedProducts($sheet): void
    {
        $products = [];
        $seenNames = [];

        foreach ($this->rows($sheet, 2) as $row) {
            $mmNumber = $this->number($row[1] ?? null);
            $description = $this->text($row[2] ?? null);
            $quantityPerBox = (int) ($row[3] ?? 0);

            if ($mmNumber === null || $description === '' || $quantityPerBox < 1) {
                continue;
            }

            if (isset($seenNames[$description])) {
                continue;
            }

            $seenNames[$description] = true;
            $products[$mmNumber] = [
                'mm_number' => $mmNumber,
                'code' => 'MM-'.$mmNumber,
                'name' => $description,
                'description' => $description,
                'qty_per_box' => $quantityPerBox,
                'is_active' => true,
            ];
        }

        foreach (array_chunk(array_values($products), 500) as $productBatch) {
            Product::query()->upsert(
                $productBatch,
                ['mm_number'],
                ['code', 'name', 'description', 'qty_per_box', 'is_active', 'updated_at'],
            );
        }
    }

    private function seedDailyReportMasters($sheet, Plant $plant, Line $line): void
    {
        $productionRows = 0;
        $maximumReports = max(1, (int) env('QA_DUMMY_MAX_REPORTS', 20));

        foreach ($this->rows($sheet, 9) as $row) {
            $machine = $this->text($row[2] ?? null);
            $mmNumber = $this->number($row[4] ?? null);
            $poNumber = $this->number($row[6] ?? null);
            $hasProduction = $machine !== '' && $mmNumber !== null && ((int) ($row[9] ?? 0) > 0 || $poNumber !== null);

            if ($hasProduction) {
                $productionRows++;

                if ($productionRows > $maximumReports) {
                    break;
                }
            }

            $machineName = $this->text($row[2] ?? null);
            $shiftValue = $this->number($row[1] ?? null);
            $defectName = $this->text($row[14] ?? null);
            $defectCategory = $this->category($row[16] ?? null);
            $checkerName = Str::upper($this->text($row[18] ?? null));

            if ($machineName !== '') {
                Machine::query()->firstOrCreate(
                    ['plant_id' => $plant->id, 'code' => $this->machineCode($machineName)],
                    ['line_id' => $line->id, 'name' => $machineName, 'is_active' => true],
                );
            }

            if ($shiftValue !== null) {
                Shift::query()->firstOrCreate(
                    ['code' => 'S'.$shiftValue],
                    [
                        'plant_id' => $plant->id,
                        'name' => 'Shift '.$shiftValue,
                        'start_time' => '00:00',
                        'end_time' => '23:59',
                        'is_active' => true,
                    ],
                );
            }

            if ($defectName !== '') {
                Defect::query()->firstOrCreate(
                    ['name' => $defectName],
                    ['code' => $this->defectCode($defectName), 'category' => $defectCategory, 'is_active' => true],
                );
            }

            if ($checkerName !== '') {
                QaChecker::query()->firstOrCreate(
                    ['employee_number' => 'QA-'.Str::upper(Str::slug($checkerName, '-'))],
                    ['plant_id' => $plant->id, 'name' => $checkerName, 'position' => 'QA Checker', 'is_active' => true],
                );
            }
        }
    }

    private function importDailyReportSheet(DailyReportExcelImport $import, $sheet): void
    {
        $chunk = new Collection();
        $productionRows = 0;
        $maximumReports = max(1, (int) env('QA_DUMMY_MAX_REPORTS', 20));

        foreach ($this->rows($sheet, 9) as $row) {
            $machine = $this->text($row[2] ?? null);
            $mmNumber = $this->number($row[4] ?? null);
            $poNumber = $this->number($row[6] ?? null);
            $hasProduction = $machine !== '' && $mmNumber !== null && ((int) ($row[9] ?? 0) > 0 || $poNumber !== null);

            if ($hasProduction) {
                $productionRows++;

                if ($productionRows > $maximumReports) {
                    break;
                }
            }

            $chunk->push($row);

            if ($chunk->count() === 250) {
                $import->collection($chunk);
                $chunk = new Collection();
            }
        }

        if ($chunk->isNotEmpty()) {
            $import->collection($chunk);
        }

        $this->command?->info("Import limit: {$maximumReports} reports.");
    }

    private function rows($sheet, int $startRow): iterable
    {
        $endRow = min(
            method_exists($sheet, 'getHighestDataRow') ? $sheet->getHighestDataRow() : $sheet->getHighestRow(),
            10000,
        );
        $emptyRows = 0;

        for ($rowNumber = $startRow; $rowNumber <= $endRow; $rowNumber++) {
            $row = $sheet->rangeToArray('A'.$rowNumber.':T'.$rowNumber, null, true, false)[0];
            $hasValue = count(array_filter($row, static fn (mixed $value): bool => $value !== null && $value !== '')) > 0;
            $emptyRows = $hasValue ? 0 : $emptyRows + 1;

            if ($emptyRows >= 50) {
                break;
            }

            if ($hasValue) {
                yield $row;
            }
        }
    }

    private function text(mixed $value): string
    {
        return trim((string) ($value ?? ''));
    }

    private function number(mixed $value): ?string
    {
        $value = $this->text($value);

        return $value === '' ? null : preg_replace('/\.0+$/', '', $value);
    }

    private function category(mixed $value): string
    {
        $category = Str::ucfirst(Str::lower($this->text($value)));

        return in_array($category, ['Critical', 'Major', 'Minor'], true) ? $category : 'Major';
    }

    private function machineCode(string $name): string
    {
        return Str::limit('M-'.Str::upper(Str::slug($name, '-')), 40, '');
    }

    private function defectCode(string $name): string
    {
        return 'DEF-'.str_pad((string) (abs(crc32($name)) % 100000), 5, '0', STR_PAD_LEFT);
    }
}
