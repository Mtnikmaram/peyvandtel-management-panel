<?php

namespace App\Services\Validators;

use App\Models\Service;
use App\Services\ServiceException;
use App\Services\ServiceValidatorInterface;

class SahabPartAISpeechToTextValidator implements ServiceValidatorInterface
{
    /**
     * @throws ServiceException
     * 
     * @return bool always true, if validation error occur it will throw ServiceException
     */
    public function validate(Service $service, int $amount, array $setting = null): bool
    {
        throw_if(!is_array($setting), new ServiceException('setting', "مقادیر به درستی تعریف نشده اند. باید قیمت به ازای ثانیه مشخص شود کلید: each_second"));

        $setting = collect($setting);

        throw_if($setting->where('key', 'each_second')->count() != 1, new ServiceException('setting', "باید قیمت به ازای ثانیه مشخص شود کلید: each_second"));

        return true;
    }
}
