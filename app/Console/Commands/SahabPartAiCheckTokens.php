<?php

namespace App\Console\Commands;

use App\Models\SahabPartAiSpeechToText;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SahabPartAiCheckTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sahabPartAiSpeechToText:checkTokens';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'check the tokens and save the result if available';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        SahabPartAiSpeechToText::query()
            ->where('status', SahabPartAiSpeechToText::$statuses[1])
            ->orderBy('updated_at', 'ASC')
            ->whereNotNull("result->token")
            ->select('id', 'result')
            ->chunk(100, function (Collection $requests) {
                $requests->each(function (SahabPartAiSpeechToText $request) {
                    $token = $request->result["token"];
                    $response = Http::acceptJson()
                        ->baseUrl('https://partai.gw.isahab.ir/speechRecognition/v1/')
                        ->withUrlParameters(["token" => $token])
                        ->get('trackingText/{token}');
                    $response = $response->json();

                    if (!self::checkApiResponse($response)) {
                        Log::critical("speech check token failed", ["body" => $response, "request" => $request]);
                        return;
                    }

                    $response = (array)$response;

                    if (!is_array($response["data"]) || !isset($response["data"]["result"])) // the file has not yet been processed
                        return;

                    $result = [
                        "text" => $response["data"]["result"]
                    ];
                    $result["timestamps"] = isset($response["data"]["time_stamp"]) ? $response["data"]["time_stamp"] : null;
                    if (isset($response["data"]["data"])) {
                        unset($response["data"]["data"]["filePath"]);
                        $result["data"] = $response["data"]["data"];
                    }


                    $request->update([
                        "status" => SahabPartAiSpeechToText::$statuses[3],
                        "result" => $result,
                    ]);
                });
            });
    }

    private static function checkApiResponse(mixed $response): bool
    {
        return is_array($response) &&
            isset($response["status"], $response["data"]) &&
            $response["status"] == "success";
    }
}
