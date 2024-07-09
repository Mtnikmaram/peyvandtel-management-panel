<?php

namespace App\Services;

use App\Exceptions\UserCreditInsufficientFund;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

abstract class ServiceProcessorBlueprint
{
    public ServiceDTO $serviceDTO;


    public function setServiceDTO(ServiceDTO $serviceDTO): self
    {
        $this->serviceDTO = $serviceDTO;
        return $this;
    }

    public function execute(): void
    {
        $this->validate();

        $this->serviceDTO->setFinalServicePrice($this->calculate());

        throw_if(!$this->checkCredit(), new UserCreditInsufficientFund($this->serviceDTO->getUser(), $this->serviceDTO->getFinalServicePrice()));

        $model = $this->storeInDB();
        throw_if(!$model || !$model instanceof Model, new RuntimeException("Internal Error", 500));
        $this->serviceDTO->setRelatedModel($model);

        throw_if(!$this->executeTheService(), new RuntimeException("There was an error in executing the request", 422));

        $this->changeCredit();
    }

    abstract protected function validate(): void;

    abstract protected function calculate(): int;

    abstract protected function checkCredit(): bool;

    abstract protected function executeTheService(): bool;

    abstract protected function changeCredit(): bool;

    abstract protected function storeInDB(): Model;
}
