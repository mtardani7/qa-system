<?php

namespace App\Imports;

use App\Models\Machine;
use App\Models\Plant;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithStartRow;

class MachineImport implements ToCollection, WithChunkReading, WithStartRow
{
    public function startRow(): int { return 2; }
    public function chunkSize(): int { return 250; }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $values = array_map(fn ($value) => trim((string) $value), array_values($row->toArray()));
            if (count($values) >= 7) {
                [$plantValue, , $code, $name, $machineNumber, $description, $active] = array_pad($values, 7, '');
            } else {
                [$plantValue, $code, $name, $machineNumber, $description, $active] = array_pad($values, 6, '');
            }
            if ($plantValue === '' || $code === '' || $name === '' || $machineNumber === '') continue;
            $plant = Plant::query()->where('is_active', true)->where(fn ($query) => $query->where('code', $plantValue)->orWhereRaw('LOWER(name) = ?', [strtolower($plantValue)]))->first();
            if (!$plant) continue;
            $line = null;
            $machine = Machine::withTrashed()->where('plant_id', $plant->id)->where('code', $code)->first() ?? Machine::withTrashed()->where('plant_id', $plant->id)->where('machine_number', $machineNumber)->first();
            $codeConflict = Machine::withTrashed()->where('plant_id', $plant->id)->where('code', $code)->when($machine, fn ($query) => $query->where('id', '<>', $machine->id))->exists();
            if ($codeConflict) continue;
            $numberConflict = Machine::withTrashed()->where('plant_id', $plant->id)->where('machine_number', $machineNumber)->when($machine, fn ($query) => $query->where('id', '<>', $machine->id))->exists();
            if ($numberConflict) continue;
            $machine ??= new Machine();
            $machine->fill(['plant_id' => $plant->id, 'line_id' => $line?->id, 'code' => $code, 'name' => $name, 'machine_number' => $machineNumber, 'description' => $description ?: null, 'is_active' => !in_array(strtolower($active), ['0', 'false', 'no', 'inactive'], true)]);
            $machine->save();
            if ($machine->trashed()) $machine->restore();
        }
    }
}
