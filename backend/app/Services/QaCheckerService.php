<?php

namespace App\Services;

use App\Repositories\Contracts\QaCheckerRepositoryInterface;

final class QaCheckerService extends BaseService
{
    public function __construct(QaCheckerRepositoryInterface $repository) { parent::__construct($repository); }
}
