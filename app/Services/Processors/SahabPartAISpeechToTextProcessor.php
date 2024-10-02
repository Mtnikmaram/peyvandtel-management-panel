<?php

namespace App\Services\Processors;

use App\Jobs\SendRequestSahabPartAiSpeechToTextJob;
use App\Models\SahabPartAiSpeechToText;
use App\Models\Service;
use App\Services\ServiceProcessorBlueprint;
use App\UserCreditHistory\Commands\SahabPartAiSpeechToTextCreditCommand;
use App\VoiceFileHelpers\VoiceFileHelper;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class SahabPartAISpeechToTextProcessor extends ServiceProcessorBlueprint
{

    /**
     * @throws Exception
     */
    protected function validate(): void
    {
        $files = $this->serviceDTO->getFiles();
        throw_if(count($files) !== 1, new Exception("one file only must be specified"));
        throw_if(!in_array(File::guessExtension($files[0]), ["mp3", "wav"]), new Exception("file must be mp3 or wav"));
    }

    protected function calculate(): int
    {
        $file = $this->serviceDTO->getFiles()[0];
        $length = (new VoiceFileHelper($file))->getDuration();
        throw_if($length === null || $length <= 0, new Exception("Couldn't calculate the file duration"));
        $this->serviceDTO->setAdditionalData("length", $length);

        $service = $this->serviceDTO->getService();
        $price = $service->price;
        throw_if(!$price, new RuntimeException("There is no price set for this service"));
        $amount = $price->amount;
        $eachSecond = collect($price->setting)->where('key', "each_second")->first()["value"];

        return ceil($length / $eachSecond) * $amount;
    }

    protected function checkCredit(): bool
    {
        $finalPrice = $this->serviceDTO->getFinalServicePrice();
        $userCredit = $this->serviceDTO->getUser()->credit;
        return $finalPrice <= $userCredit;
    }

    protected function storeInDB(): Model
    {
        //save the file using uuid
        $file = $this->serviceDTO->getFiles()[0];
        $file = Storage::putFile(Service::getDirectoryPath(), $file);
        throw_if(!$file, new RuntimeException("Cannot write the file", 500));
        $file = basename($file);

        // start a transaction an end it in changeCredit method
        DB::beginTransaction();


        return SahabPartAiSpeechToText::query()
            ->create([
                "id" => $this->serviceDTO->uuid,
                "user_id" => $this->serviceDTO->getUser()->id,
                "used_credit" => $this->serviceDTO->getFinalServicePrice(),
                "file" => $file,
                "file_length" => $this->serviceDTO->getAdditionalData('length'),
                "payload" => $this->serviceDTO->getPayload()
            ])
            ->refresh();
    }

    protected function executeTheService(): bool
    {
        $model = $this->serviceDTO->getRelatedModel() ?: SahabPartAiSpeechToText::query()->find($this->serviceDTO->uuid);
        throw_if(!$model, new RuntimeException("There was an error in fetching data from db"));

        if ($model->is_short_file) {
            SendRequestSahabPartAiSpeechToTextJob::dispatchSync($model);
            $model->refresh();
            $this->serviceDTO
                ->setResultIsReady()
                ->setFinalResult(["text" => $model->result['text']]);
        } else {
            SendRequestSahabPartAiSpeechToTextJob::dispatch($model)
                ->afterCommit() // it will be created when the transaction started in the storeToDB is committed in changeCredit, in case any problem happens on changing the credit
                ->delay(now()->addSeconds(5))
                ->onQueue('services');
        }

        return true;
    }

    protected function changeCredit(): bool
    {
        try {
            $id = $this->serviceDTO->uuid;
            $user = $this->serviceDTO->getUser();
            $amount = $this->serviceDTO->getFinalServicePrice();
            SahabPartAiSpeechToTextCreditCommand::execute($user, $amount, "کاهش وجه بابت درخواست سرویس تبدیل صوت به متن. ID: $id");
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }

        // here we must commit the transaction in order to start the job
        DB::commit();
        return true;
    }

    public function verifyApiResponse(mixed $response, bool $isShortFileResponse = false): bool
    {
        if ($isShortFileResponse) {
            /*
            * [
            *   "data" =>  [
            *       "status" => "success",
            *       "data" => [
            *           "result" => '....',
            *           "time_stamp" => [],
            *       ],
            *       "requestId" => "**********"
            *   ],
            *   "meta" => [
            *       "shamsiDate" => "140304*********",
            *       "requestId" => "*********"
            *   ],
            * ]
            */
            return is_array($response) &&
                isset($response["data"]) &&
                isset($response["data"]["status"], $response["data"]["data"]) &&
                $response["data"]["status"] === "success" &&
                is_array($response["data"]["data"]) &&
                count($response["data"]["data"]) &&
                isset($response["data"]["data"]["result"]);
        } else {

            /*
            * [
            *   "data" =>  [
            *       "status" => "success",
            *       "data" => [
            *           "token" => "token_********"
            *       ],
            *       "requestId" => "**********"
            *   ],
            *   "meta" => [
            *       "shamsiDate" => "140304*********",
            *       "requestId" => "*********"
            *   ],
            * ]
            */
            return is_array($response) &&
                isset($response["data"]) &&
                isset($response["data"]["status"], $response["data"]["data"]) &&
                $response["data"]["status"] === "success" &&
                is_array($response["data"]["data"]) &&
                count($response["data"]["data"]) &&
                isset($response["data"]["data"]["token"]);
        }
    }
}
