<?php

namespace App\Jobs;

use Closure;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const DISPLAY_NAME = "ارسال پیامک";

    public $backoff = 5;
    public $tries = 5;


    /**
     * Create a new job instance.
     */
    public function __construct(
        private string $method,
        private string $url,
        private array $data,
        private array $isSuccessfulCallBack,
        private bool $asForm = false,
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (!in_array($this->method, ["get", "post"])) {
            $this->fail("$this->method is no valid. must be get or post");
            return;
        }

        $request = Http::acceptJson()
            ->retry(3, 1);

        if ($this->asForm)
            $request = $request->asForm();

        $request = match ($this->method) {
            "get" => $request->get($this->url, $this->data),
            "post" => $request->post($this->url, $this->data)
        };

        if (!isset($request) || !$request->successful() || !call_user_func($this->isSuccessfulCallBack, $request->json())) {
            Log::warning("Sms can not be sent.", ["status" => $request->status(), "req" => $request->json(), 'data' => $this->data, 'trace' => debug_backtrace()]);
            $this->fail("Sms can not be sent. JSON:" . $request->body());
            return;
        }
    }
}
