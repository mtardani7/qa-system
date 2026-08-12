<?php
namespace App\Services;
use App\Repositories\Contracts\LineRepositoryInterface;
final class LineService extends BaseService { public function __construct(LineRepositoryInterface $repository) { parent::__construct($repository); } }
