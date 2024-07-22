<?php

namespace App\Observers;

use App\Models\UserCreditHistory;
use App\SMS\SmsData;
use App\SMS\SmsProvider;
use App\SMS\SmsTemplatesEnum;

class UserCreditHistoryObserver
{
    /**
     * Handle the UserCreditHistory "created" event.
     */
    public function created(UserCreditHistory $userCreditHistory): void
    {
        $user = $userCreditHistory->user;

        if ($userCreditHistory->updated_credit <= $user->credit_threshold)
            SmsProvider::getProvider()
                ->setData(
                    new SmsData(
                        reception: $user->phone,
                        templateId: SmsTemplatesEnum::CreditLessThanThreshold,
                        tokens: [$user->name]
                    )
                )
                ->sendTemplateSms();
    }

    /**
     * Handle the UserCreditHistory "updated" event.
     */
    public function updated(UserCreditHistory $userCreditHistory): void
    {
        //
    }

    /**
     * Handle the UserCreditHistory "deleted" event.
     */
    public function deleted(UserCreditHistory $userCreditHistory): void
    {
        //
    }

    /**
     * Handle the UserCreditHistory "restored" event.
     */
    public function restored(UserCreditHistory $userCreditHistory): void
    {
        //
    }

    /**
     * Handle the UserCreditHistory "force deleted" event.
     */
    public function forceDeleted(UserCreditHistory $userCreditHistory): void
    {
        //
    }
}
