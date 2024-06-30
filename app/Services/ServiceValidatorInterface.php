<?php

namespace App\Services;

use App\Models\Service;

interface ServiceValidatorInterface
{
    /**
     * @throws ServiceException
     */
    public function validate(Service $service, int $amount, array $setting = null, bool $updating = false): bool;
}
