<?php

namespace App\Services\Repositories;

use App\Models\SahabPartAiSpeechToText;
use App\Models\User;
use App\Services\ServicesRepositoryInterface;
use Illuminate\Contracts\Database\Eloquent\Builder;

class SahabPartAISpeechToTextRepository implements ServicesRepositoryInterface
{
    private User $user;
    private array $search = [];

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function setSearchAttribute(string $key, mixed $value): self
    {
        $this->search[$key] = $value;

        return $this;
    }

    public function paginatedList(int $page): object
    {
        return $this->getBaseQueryBuilder()
            ->when(isset($this->search['payload']), fn ($q) => $q->whereJsonContains('payload', $this->search["payload"]))
            ->when(isset($this->search['from']), fn ($q) => $q->where('updated_at', ">=", $this->search["from"]))
            ->latest()
            ->paginate(perPage: 15, page: $page);
    }


    public function all(): array
    {
        return $this->getBaseQueryBuilder()
            ->when(isset($this->search['payload']), fn ($q) => $q->whereJsonContains('payload', $this->search["payload"]))
            ->when(isset($this->search['from']), fn ($q) => $q->where('updated_at', ">=", $this->search["from"]))
            ->latest()
            ->get()
            ->toArray();
    }

    public function show(string|int $id): object
    {
        return $this->getBaseQueryBuilder()
            ->when(isset($this->search['payload']), fn ($q) => $q->whereJsonContains('payload', $this->search["payload"]))
            ->when(isset($this->search['from']), fn ($q) => $q->where('updated_at', ">=", $this->search["from"]))
            ->find($id);
    }

    private function getBaseQueryBuilder(): Builder
    {
        return SahabPartAiSpeechToText::query()
            ->where('user_id', $this->user->id)
            ->selectRaw("*, 
                    (CASE
                        WHEN status = '" . SahabPartAiSpeechToText::$statuses[1] . "' Then null
                        ELSE result
                    END) AS result");
    }
}
