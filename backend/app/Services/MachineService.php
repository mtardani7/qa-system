<?php
namespace App\Services;
use App\Repositories\Contracts\MachineRepositoryInterface;
final class MachineService extends BaseService { public function __construct(MachineRepositoryInterface $repository) { parent::__construct($repository); } }
