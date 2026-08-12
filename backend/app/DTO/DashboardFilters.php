<?php

namespace App\DTO;

use Carbon\CarbonImmutable;

final readonly class DashboardFilters
{
    public function __construct(public CarbonImmutable $date, public ?int $plantId = null, public ?int $shiftId = null, public ?int $machineId = null, public ?int $lineId = null, public ?int $productId = null, public ?int $checkerId = null, public ?CarbonImmutable $dateFrom = null, public ?CarbonImmutable $dateTo = null, public string $period = 'daily') {}

    public function from(): CarbonImmutable { return $this->dateFrom ?? $this->date; }
    public function to(): CarbonImmutable { return $this->dateTo ?? $this->date; }
    public function key(): array { return ['date' => $this->date->toDateString(), 'from' => $this->from()->toDateString(), 'to' => $this->to()->toDateString(), 'period' => $this->period, 'plant' => $this->plantId, 'line' => $this->lineId, 'machine' => $this->machineId, 'shift' => $this->shiftId, 'product' => $this->productId, 'checker' => $this->checkerId]; }
}
