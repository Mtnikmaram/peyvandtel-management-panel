<?php

return [
    "activeProvider" => env('SMS_PROVIDER', 'FaraPayamak'),
    "configs" => [
        "FaraPayamak" => [
            "username" => env('FARAPAYAMAK_USERNAME', false),
            "password" => env('FARAPAYAMAK_PASSWORD', false),
            "sender_number" => env('FARAPAYAMAK_SENDER', false),
        ]
    ],
    "templateIds" => [
        "FaraPayamak" => [
            "credit_less_than_threshold" => 193759,
            // "credit_insufficient" => null
        ]
    ]
];
