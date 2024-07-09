<?php

namespace App\Exceptions;

use App\Models\User;
use Exception;

class UserCreditInsufficientFund extends Exception
{

    /**
     * Create a new exception instance.
     *
     * @param  User  $user
     * @param  mixed  $id
     * @return void
     */
    public function __construct(User $user, mixed $neededCredit)
    {
        $id = (string) $user->id;
        $credit = (string) $user->credit;
        $neededCredit = (string) $neededCredit;

        parent::__construct("Insufficient Fund. User with ID $id needs $neededCredit but has $credit");
    }
}
