<?php

namespace App\Services;

use App\Models\Service;
use App\Models\User;
use Exception;

class ServiceFactory
{
    /**
     * @param Service $service
     *
     * @throws \Throwable
     */
    public static function getValidator(Service $service)
    {
        $className = "App\\Services\\Validators\\" . $service->id . "Validator";
        throw_if(!class_exists($className), new Exception("Validator class does not exist", 500));

        return new $className();
    }

    public static function execute(ServiceDTO $serviceDTO): void
    {
        $className = "App\\Services\\Processors\\" . $serviceDTO->getServiceId() . "Processor";
        throw_if(!class_exists($className), new Exception("processor class does not exist", 500));
        /** @var ServiceProcessorBlueprint $processor */
        (new $className())->setServiceDTO($serviceDTO)->execute();
    }
}
