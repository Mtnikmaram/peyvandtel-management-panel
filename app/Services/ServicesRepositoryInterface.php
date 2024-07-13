<?php

namespace App\Services;

use App\Models\User;

interface ServicesRepositoryInterface
{
    public function setUser(User $user): self;

    public function setSearchAttribute(string $key, mixed $value): self;

    public function paginatedList(int $page): object;

    public function all(): array;

    public function show(string|int $id): object;
}
