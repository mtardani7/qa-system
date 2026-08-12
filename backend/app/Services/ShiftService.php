<?php
namespace App\Services;
use App\Repositories\Contracts\ShiftRepositoryInterface;
final class ShiftService extends BaseService { public function __construct(ShiftRepositoryInterface $repository) { parent::__construct($repository); } }
