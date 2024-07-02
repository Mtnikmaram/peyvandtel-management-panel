<?php

namespace App\UserCredits;

use App\Models\User;

interface UserCreditCommandInterface
{
    public function execute(User $user, string $type): void;

    public function check(User $user, string $type): bool;

    public function getList(bool $excludeExclusives = false): array;

    public function typeIsRelated(string $type): bool;
}
