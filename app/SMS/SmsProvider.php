<?php

namespace App\SMS;

use App\SMS\Providers\FaraPayamak;
use Exception;

/**
 * Abstract class for SMS providers.
 *
 * This abstract class provides a blueprint for implementing SMS providers.
 * It includes common methods and properties shared by SMS providers.
 *
 * @package App\SMS
 */
abstract class SmsProvider
{
    /**
     * List of available SMS provider classes.
     *
     * @var array
     */
    private static $providers = [
        "FaraPayamak",
        // "newProvider",
    ];

    /**
     * Mapping of provider names to their human-readable labels.
     *
     * @var array
     */
    public static $providerNames = [
        "FaraPayamak" => "فراپیامک",
        // "newProvider" => "name to be shown of newProvider",
    ];

    /**
     * SMS data to be sent.
     *
     * @var SmsData $smsData
     */
    protected $smsData;

    /**
     * Get an instance of the appropriate SMS provider based on configuration.
     *
     * @return SmsProvider An instance of the SMS provider.
     *
     * @throws Exception If the configuration is missing or the provider is not supported.
     */
    public static function getProvider(): SmsProvider
    {
        $provider = self::getActiveProvider();
        switch ($provider) {
            case "FaraPayamak": {
                    $config = config('sms.configs.FaraPayamak');
                    return new FaraPayamak($config);
                }
                // case "newProvider": {
                // return new newProvider();
                // }
        }
    }

    public static function getTemplateId(SmsTemplatesEnum $template): string
    {
        $templateKey = $template->value;
        $provider = self::getActiveProvider();
        $template = config("sms.templateIds.$provider.$templateKey");
        throw_if(!$template, new Exception("The template id is not set for the active provider. active provider: $provider"));

        return $template;
    }

    private static function getActiveProvider(): string
    {
        $provider = config('sms.activeProvider');
        throw_if(!$provider, new Exception("Specify the SMS PROVIDER", 500));
        throw_if(!in_array($provider, self::$providers), new Exception("Wrong SMS Provider. This SMS provider ($provider) is not supported", 500));
        return $provider;
    }

    /**
     * Send an SMS request to the provider.
     *
     * @param SmsData $smsData the data of the sms that wanted to be sent
     *
     * @return SmsProvider
     */
    abstract public function setData(SmsData $smsData): SmsProvider;

    /**
     * Send an SMS request to the provider.
     *
     * @param string $action The action to perform.
     * @param array $data The data to send with the request.
     *
     * @return array|bool The response from the provider.
     */
    abstract protected function sendRequest($action, $data): array|bool;

    /**
     * Check the configuration settings for the SMS provider.
     * 
     * @return bool whether the config is completed or not
     */
    abstract protected function checkConfig(array $config): bool;

    /**
     * Check if the SMS sending was successful based on the provider's response.
     *
     * @param mixed $responseResult The result from the provider's response.
     *
     * @return bool True if the sending was successful; otherwise, false.
     */
    abstract function isSuccessful(mixed $responseResult): bool;

    /**
     * Send a single or group SMS.
     * 
     * if the message and phone in SmsData is string send single sms.
     * if they are arrays send group SMS.
     */
    abstract function sendSms(): void;

    /**
     * Send an SMS based on a template that is defined in providers platform.
     */
    abstract function sendTemplateSms(): void;

    /**
     * this method will get the all sms that the provider has received
     * 
     * @return array the list of received sms
     */
    abstract function getReceivedSms(): array;
}
