<?php

namespace App\Services;

use App\Models\Plant;
use App\Repositories\Contracts\PlantRepositoryInterface;

final class PlantService extends BaseService
{
    public function __construct(PlantRepositoryInterface $plants) { parent::__construct($plants); }
}
