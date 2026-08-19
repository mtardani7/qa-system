<?php
namespace App\Imports;
use App\Models\Product; use Illuminate\Support\Collection; use Maatwebsite\Excel\Concerns\ToCollection; use Maatwebsite\Excel\Concerns\WithStartRow;
class DailyReportProductMasterImport implements ToCollection, WithStartRow
{
	private array $columns = [];
	private array $productNameCache = [];

	public function startRow(): int { return 1; }

	public function collection(Collection $rows): void
	{
		foreach ($rows as $row) {
			$values = array_values($row->toArray());
			if ($this->columns === []) {
				$this->columns = $this->headers($values);
				continue;
			}

			$mm = $this->number($values[$this->columns['mm']] ?? null);
			$description = $this->text($values[$this->columns['description']] ?? null);
			$qty = $this->quantity($values[$this->columns['qty']] ?? null);
			if ($mm === null || $description === '' || $qty < 1) continue;

			$nameKey = $this->key($description);
			$product = Product::withTrashed()->where('mm_number', $mm)->first();
			if (!$product) $product = $this->productNameCache[$nameKey] ?? Product::withTrashed()->whereRaw('LOWER(name) = ?', [strtolower($description)])->first();
			if (!$product) {
				$product = Product::withTrashed()->get(['id', 'name', 'mm_number', 'code', 'description', 'qty_per_box', 'is_active', 'deleted_at'])->first(fn (Product $item) => $this->key($item->name) === $nameKey);
			}
			$product ??= new Product();
			$product->fill([
				'code' => $product->code ?: 'MM-'.$mm,
				'name' => $description,
				'mm_number' => $mm,
				'description' => $description,
				'qty_per_box' => $qty,
				'is_active' => true,
			]);
			$product->save();
			if ($product->trashed()) $product->restore();
			$this->productNameCache[$nameKey] = $product;
		}
	}

	private function headers(array $values): array
	{
		$headers = array_map(fn ($value) => strtoupper($this->text($value)), $values);
		$find = function (array $names) use ($headers): int {
			foreach ($names as $name) {
				$index = array_search($name, $headers, true);
				if ($index !== false) return $index;
			}
			return -1;
		};
		return [
			'mm' => max(0, $find(['MM', 'NO. MM', 'MM NUMBER'])),
			'description' => max(0, $find(['DESCRIPTION', 'DESCRIPTION ITEM', 'NAMA ITEM'])),
			'qty' => max(0, $find(['/BOX', 'QTY /BOX', 'QTY PER BOX', 'QTY/BOX'])),
		];
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
