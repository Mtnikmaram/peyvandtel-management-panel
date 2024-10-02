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

    /**
     * @param ServiceDTO $serviceDTO
     *
     * @throws \Throwable
     */
    public static function execute(ServiceDTO $serviceDTO): ServiceDTO
    {
        $className = "App\\Services\\Processors\\" . $serviceDTO->getServiceId() . "Processor";
        throw_if(!class_exists($className), new Exception("processor class does not exist", 500));
        /** @var ServiceProcessorBlueprint $processor */
        return (new $className())->setServiceDTO($serviceDTO)->execute();
    }

    /**
     * @param Service $service
     * @param mixed $apiResponse
     * 
     * @return bool
     */
    public static function verifyApiResponse(Service $service, ...$arguments): bool
    {
        $className = "App\\Services\\Processors\\" . $service->id . "Processor";
        throw_if(!class_exists($className), new Exception("processor class does not exist", 500));
        return !method_exists($className, 'verifyApiResponse') || (new $className())->verifyApiResponse(...$arguments);
    }

    public static function getServiceRepository(Service $service, User $user): ServicesRepositoryInterface
    {
        $className = "App\\Services\\Repositories\\" . $service->id . "Repository";
        throw_if(!class_exists($className), new Exception("repository class does not exist", 500));
        return (new $className())->setUser($user);
    }
}
