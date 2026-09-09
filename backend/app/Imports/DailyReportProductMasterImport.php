<?php
namespace App\Imports;
use App\Models\Product; use Illuminate\Support\Collection; use Maatwebsite\Excel\Concerns\ToCollection; use Maatwebsite\Excel\Concerns\WithChunkReading; use Maatwebsite\Excel\Concerns\WithStartRow;
class DailyReportProductMasterImport implements ToCollection, WithChunkReading, WithStartRow
{
	private array $columns = [];
	private array $seenMm = [];

	public function __construct(private readonly int $plantId) {}

	public function startRow(): int { return 1; }
	public function chunkSize(): int { return 250; }

	public function collection(Collection $rows): void
	{
		foreach ($rows as $row) {
			$values = array_values($row->toArray());
			if ($this->columns === []) {
				$columns = $this->headers($values);
				if ($columns !== null) {
					$this->columns = $columns;
					continue;
				}
				if ($this->columns === []) continue;
			}

			$plantId = $this->plantId;
			$mm = $this->number($values[$this->columns['mm']] ?? null);
			$description = $this->text($values[$this->columns['description']] ?? null);
			$qty = $this->quantity($values[$this->columns['qty']] ?? null);
			if ($mm === null || $description === '' || $qty < 1) continue;
			$seenKey = ($plantId ?? 'shared').':'.$mm;
			if (isset($this->seenMm[$seenKey])) continue;
			$this->seenMm[$seenKey] = true;

			$product = Product::withTrashed()->where('mm_number', $mm)->when($plantId, fn ($query) => $query->where('plant_id', $plantId), fn ($query) => $query->whereNull('plant_id'))->first();
			if (!$product && Product::withTrashed()->whereRaw('LOWER(name) = ?', [strtolower($description)])->when($plantId, fn ($query) => $query->where('plant_id', $plantId), fn ($query) => $query->whereNull('plant_id'))->exists()) continue;
			if ($product && Product::withTrashed()->whereRaw('LOWER(name) = ?', [strtolower($description)])->when($plantId, fn ($query) => $query->where('plant_id', $plantId), fn ($query) => $query->whereNull('plant_id'))->where($product->getKeyName(), '<>', $product->getKey())->exists()) continue;
			$code = $product->code ?? 'MM-'.$mm;
			if (!$product && Product::withTrashed()->where('code', $code)->when($plantId, fn ($query) => $query->where('plant_id', $plantId), fn ($query) => $query->whereNull('plant_id'))->exists()) continue;
			$product ??= new Product();
			$product->fill([
				'plant_id' => $plantId,
				'code' => $product->code ?: $code,
				'name' => $description,
				'mm_number' => $mm,
				'description' => $description,
				'qty_per_box' => $qty,
				'is_active' => true,
			]);
			$product->save();
			if ($product->trashed()) $product->restore();
		}
	}

	private function headers(array $values): ?array
	{
		$headers = array_map(fn ($value) => strtoupper($this->text($value)), $values);
		$find = function (array $names) use ($headers): int {
			foreach ($names as $name) {
				$index = array_search($name, $headers, true);
				if ($index !== false) return $index;
			}
			return -1;
		};
		$columns = [
			'mm' => $find(['MM', 'NO. MM', 'MM NUMBER']),
			'description' => $find(['DESCRIPTION', 'DESCRIPTION ITEM', 'NAMA ITEM']),
			'qty' => $find(['/BOX', 'QTY /BOX', 'QTY PER BOX', 'QTY/BOX']),
		];
		return in_array(-1, [$columns['mm'], $columns['description'], $columns['qty']], true) ? null : $columns;
	}

	private function text(mixed $value): string
	{
		$value = trim(str_replace(["\xc2\xa0", "\xa0"], ' ', (string) $value));
		return str_starts_with($value, '=') ? '' : $value;
	}

	private function key(mixed $value): string
	{
		return strtolower(preg_replace('/\s+/u', ' ', $this->text($value)));
	}

	private function number(mixed $value): ?string
	{
		$value = $this->text($value);
		if ($value === '') return null;
		return preg_replace('/\.0+$/', '', $value);
	}

	private function quantity(mixed $value): int
	{
		$value = str_replace(',', '.', $this->text($value));
		return is_numeric($value) ? (int) $value : 0;
	}
}
