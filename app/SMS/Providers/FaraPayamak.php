<?php

namespace App\SMS\Providers;

use App\Jobs\SendSmsJob;
use App\SMS\ReceiveSmsData;
use App\SMS\SmsData;
use App\SMS\SmsProvider;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FaraPayamak extends SmsProvider
{
    private $username;
    private $password;
    private $senderNumber;
    private static $apiUrl = "https://rest.payamak-panel.com/api/SendSMS/";



    public function __construct(array $config)
    {
        if ($this->checkConfig($config)) {
            $this->username = $config["username"];
            $this->password = $config["password"];
            $this->senderNumber = $config["sender_number"];
        } else
            throw new Exception("تنظمیات فراپیامک به درستی انجام نشده است");
    }

    public function setData(SmsData $smsData): SmsProvider
    {
        $this->smsData = $smsData;
        return $this;
    }

    protected function checkConfig(array $config): bool
    {
        return (is_array($config) &&
            isset($config["username"]) &&
            isset($config["password"]) &&
            isset($config["sender_number"])
        );
    }

    protected function sendRequestInQueue($action, $data): void
    {
        $data["username"] = $this->username;
        $data["password"] = $this->password;
        SendSmsJob::dispatch("post", self::$apiUrl . $action, $data, [$this, "isSuccessful"])
            ->onQueue("sms")
            ->delay(now()->addSeconds(1));
    }

    protected function sendRequest($action, $data, $method = "post", $returnResponse = false): array|bool
    {
        $data["username"] = $this->username;
        $data["password"] = $this->password;
        $url = self::$apiUrl . $action;

        $response = Http::retry(3, 1)
            ->acceptJson()
            ->asForm();

        if ($method == "post")
            $response = $response->post($url, $data);
        else if ($method == "get")
            $response = $response->get($url, $data);

        if ($returnResponse)
            return $response->json();
        else {
            if ($this->isSuccessful($response->json()))
                return true;
            else {
                Log::critical('FaraPayamak did not successfully ended the request.', ["response" => $response]);
                return false;
            }
        }
    }

    public function isSuccessful($responseResult): bool
    {
        // for sending one sms
        $oneSms = is_array($responseResult) &&
            isset($responseResult["StrRetStatus"]) &&
            isset($responseResult["RetStatus"]) &&
            strtolower($responseResult["StrRetStatus"]) == "ok" &&
            $responseResult["RetStatus"] == 1;
        // for sending group sms
        $groupSms = false;
        if (!$oneSms) {
            $groupSms = is_array($responseResult) &&
                isset($responseResult["Result"]);
            if ($groupSms)
                foreach ($responseResult["Result"] as $r) {
                    $groupSms = $groupSms &&
                        is_array($r) &&
                        isset($r["ReqID"]) &&
                        isset($r["ReqStatus"]) &&
                        $r["ReqStatus"] == 1;
                }
        }

        // this function is for validating send state
        // this function works if we do not use Queues to send the Http request
        return $oneSms || $groupSms;
    }

    public function sendTemplateSms(): void
    {
        $tokens = $this->smsData->getTokens();
        foreach ($tokens as $k => $t)
            $tokens[$k] = str_replace(";", "", $t);
        $data = [
            "to" => $this->smsData->getReception(),
            "bodyId" => $this->smsData->getTemplateId(),

            "text" => implode(";", $tokens),
        ];
        $this->sendRequestInQueue("BaseServiceNumber", $data);
    }

    public function sendSms(): void
    {
        $phones = $this->smsData->getReception();
        $messages = $this->smsData->getMessage();

        //check if the phones is array so the messages must be array too vice versa
        if ((is_array($phones) && !is_array($messages)) || (!is_array($phones) && is_array($messages)) || (is_array($phones) && is_array($messages) && count($phones) != count($messages)))
            throw new Exception("both phones and messages must be array with same length");

        if (is_array($phones) && is_array($messages) && count($phones) == count($messages))
            $this->sendGroupMessage();
        else {
            $data = [
                "from" => $this->senderNumber,
                "to" => $phones,
                "text" => $messages,
            ];
            $this->sendRequestInQueue("SendSMS", $data);
        }
    }

    /**
     * for group send
     * phones and messages and sender numbers must be arrays of string with same length
     */
    public function sendGroupMessage()
    {
        $phones = $this->smsData->getReception();
        $messages = $this->smsData->getMessage();

        $data = [
            "from" => $this->senderNumber,
            "to" => ($phones),
            "text" => ($messages), // if error happens in some situations do urlencode on each of messages values
        ];

        $this->sendRequestInQueue("SendMultipleSMS", $data, "post", true);
        return true;
    }

    public function  getReceivedSms(): array
    {
        //TODO: not implemented yet
        return [];
    }
}
