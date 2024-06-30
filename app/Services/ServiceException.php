<?php

namespace App\Services;

use Exception;

class ServiceException extends Exception
{
    public function __construct(
        protected string $errorName = "service_exception",
        protected $message = "",
        protected $code = 422,
    ) {
        parent::__construct($message, $code);
    }

    public function getErrorName(): string
    {
        return $this->errorName;
    }
}
