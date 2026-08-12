<?php
namespace App\Services;
use App\Repositories\EnterpriseRepository; use Illuminate\Database\Eloquent\Model;
class EnterpriseService extends BaseService { public function __construct(EnterpriseRepository $repository){parent::__construct($repository);} public function find(int $id):Model{return $this->repository->findOrFail($id);} }
