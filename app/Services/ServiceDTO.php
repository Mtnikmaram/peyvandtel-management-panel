<?php

namespace App\Services;

use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

final class ServiceDTO
{
    public readonly string $uuid;
    private Service $serviceModel;
    private int $finalServicePrice = -1;
    private array $additionalData = [];
    private ?Model $relatedModel = null;

    public function __construct(
        private string $serviceId,
        private User $currentUser,
        private array $payload = [],
        private array $files = []
    ) {
        $this->serviceModel = Service::query()->find($this->serviceId);
        throw_if(!$this->serviceModel, new InvalidArgumentException("service identifier is not valid", 422));

        $this->uuid = Str::orderedUuid();
    }

    public function getServiceId(): string
    {
        return $this->serviceId;
    }

    public function getService(): Service
    {
        return $this->serviceModel;
    }

    public function getUser(): User
    {
        return $this->currentUser;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function setAdditionalData(string $key, int $price): self
    {
        $this->additionalData[$key] = $price;
        return $this;
    }

    public function getAdditionalData(string $key): int
    {
        throw_if(!isset($this->additionalData[$key]), new RuntimeException("$key is not set"));
        return $this->additionalData[$key];
    }

    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * This function can be used once per object instantiation
     * 
     * @param int $price must be an positive number
     * 
     * @return void
     */
    public function setFinalServicePrice(int $price): void
    {
        if ($price >= 0 && $this->finalServicePrice < 0)
            $this->finalServicePrice = $price;
    }

    public function getFinalServicePrice(): int
    {
        return $this->finalServicePrice;
    }

    public function setRelatedModel(Model $model): self
    {
        $this->relatedModel = $model;
        return $this;
    }

    public function getRelatedModel(): Model
    {
        return $this->relatedModel;
    }
}
