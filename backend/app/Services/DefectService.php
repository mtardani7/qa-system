<?php
namespace App\Services;
use App\Repositories\Contracts\DefectRepositoryInterface;
final class DefectService extends BaseService { public function __construct(DefectRepositoryInterface $repository) { parent::__construct($repository); } }
