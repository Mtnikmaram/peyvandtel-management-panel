<?php

namespace App\SMS;

enum SmsTemplatesEnum:string{
    case CreditLessThanThreshold = "credit_less_than_threshold";
    case CreditInsufficient = "credit_insufficient";
}