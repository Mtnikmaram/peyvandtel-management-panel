<?php

namespace App\Services;

use App\Models\Service;
use Exception;

class ServiceFactory
{
    /**
     * @param Service $service
     *
     * @return ServiceValidatorInterface
     * @throws \Throwable
     */
    public static function getValidator(Service $service): ServiceValidatorInterface
    {
        $className = "App\\Services\\Validators\\" . $service->id . "Validator";
        throw_if(!class_exists($className), new Exception("Validator class does not exist", 500));

        return new $className();
    }
}
