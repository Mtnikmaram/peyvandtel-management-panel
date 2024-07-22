<?php

namespace App\UserCreditHistory;

use App\Models\User;
use App\Models\UserCreditHistory;
use Illuminate\Database\Eloquent\Model;

interface UserCreditHistoryCommandInterface
{
    public static function getTypeName(): string;

    public static function getTypeShownName(): string;
    
    public static function execute(User $user, int $amount, string $description = null): UserCreditHistory;

    public static function revert(User $user, int $amount, string $description = null): UserCreditHistory;
}
