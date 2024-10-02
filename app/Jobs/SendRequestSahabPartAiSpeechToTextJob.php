<?php

namespace App\Jobs;

use App\Models\SahabPartAiSpeechToText;
use App\Models\Service;
use App\Services\ServiceFactory;
use App\UserCreditHistory\Commands\SahabPartAiSpeechToTextCreditCommand;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class SendRequestSahabPartAiSpeechToTextJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Delete the job if its models no longer exist.
     *
     * @var bool
     */
    public $deleteWhenMissingModels = true;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 10;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 120; // 2 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(
        public SahabPartAiSpeechToText $model
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $service = Service::query()->where('id', Service::$services[0]['id'])->first();
        if (!$service || !$service->is_active) {
            $this->fail(new RuntimeException("The service cannot be found or is disabled."));
            return;
        }

        $credential = $service->credential; // token

        $url = $this->model->is_short_file ? 'file' : 'largeFile';

        $response = null;
        try {
            $filePath = Storage::path($this->model->filePath);

            $response = Http::acceptJson()
                ->timeout(90)
                ->withOptions([
                    'allow_redirects' => true, // Follow redirects
                    'expect' => false, // Disable the 'Expect: 100-continue' header
                ])
                ->withHeaders([
                    'gateway-token' => $credential
                ])
                ->attach( // make request a MultiPart request 
                    'file',
                    fopen($filePath, 'rb'), // absolute path to file
                    basename($filePath)
                )
                ->baseUrl('https://partai.gw.isahab.ir/speechRecognition/v1/')
                ->post($url, [
                    'language' => 'fa',
                    'model' => 'telephony',
                ]);
        } catch (Exception $e) {
            $this->fail($e);
            return;
        }

        if (!$response->successful()) {
            Log::critical('speechRecognition', ["status" => $response->status(), "body" => $response->body()]);
            $this->fail(new Exception("error in sending the request"));
            return;
        }

        $responseArray = (array)$response->json();
        if (!ServiceFactory::verifyApiResponse($service, $responseArray, $this->model->is_short_file)) {
            Log::critical('speechRecognition not valid response', ["status" => $response->status(), "body" => $response->body()]);
            $this->fail(new Exception("speechRecognition not valid response"));
            return;
        }

        try {
            if ($this->model->is_short_file) {
                $responseArray = $responseArray["data"];
                $result = [
                    "text" => $responseArray["data"]["result"]
                ];
                $result["timestamps"] = isset($responseArray["data"]["time_stamp"]) ? $responseArray["data"]["time_stamp"] : null;
                if (isset($responseArray["data"]["data"])) {
                    unset($responseArray["data"]["data"]["filePath"]);
                    $result["data"] = $responseArray["data"]["data"];
                }


                $this->model->update([
                    "status" => SahabPartAiSpeechToText::$statuses[3],
                    "result" => $result,
                ]);
            } else {
                $token = $responseArray["data"]["data"]["token"];
                $this->model->update([
                    "status" => SahabPartAiSpeechToText::$statuses[1],
                    "result" => ["token" => $token]
                ]);
            }
        } catch (Exception $e) {
            $this->fail($e);
            return;
        }
    }

    /**
     */
    public function fail($exception = null): void
    {
        $this->delete(); // do not insert the job into the failed jobs table

        if ($exception instanceof Throwable)
            Log::critical('speechRecognition', ["message" => $exception->getMessage(), "line" => $exception->getLine(), "file" => $exception->getFile()]);

        if ($this->model->exists)
            $this->model->update([
                "status" => SahabPartAiSpeechToText::$statuses[2],
            ]);

        $id = $this->model->id;
        SahabPartAiSpeechToTextCreditCommand::revert($this->model->user, $this->model->used_credit, "عودت وجه کسر شده. عملیات سرویس تبدیل صوت به متن با مشکل مواجه شد. ID: $id");
        return;
    }
}
