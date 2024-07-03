<?php

namespace App\SMS;

use Exception;

class SmsData
{
    /**
     * either message is present or template id
     * with message we should send a regular api call 
     * with template we must use tokens and send a predefined message in that provider
     */
    private ?string $templateId;

    public function __construct(
        private string|array $reception,
        private string|array|null $message = null,
        SmsTemplatesEnum $templateId = null,
        private array $tokens = []
    ) {
        if (!is_numeric($reception) && !is_array($reception))
            throw new Exception("reception ($reception) does not have a valid format");

        $this->templateId = SmsProvider::getTemplateId($templateId);
    }

    public function getReception(): string|array
    {
        return $this->reception;
    }

    public function getMessage(): string|array|null
    {
        return $this->message;
    }

    public function getTemplateId(): string|null
    {
        return $this->templateId;
    }

    public function getTokens(): array
    {
        return $this->tokens;
    }
}
