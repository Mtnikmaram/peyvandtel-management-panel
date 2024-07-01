<?php


namespace App\SMS;

use Carbon\Carbon;

class ReceiveSmsData
{

    public function __construct(
        public string $id,
        public string $senderNumber,
        public string $text,
        public ?Carbon $sentAt = null,
        public ?string $receptor = null
    ) {
    }
}
