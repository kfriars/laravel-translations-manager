<?php

return [
    'de' => [
        'mail/auth/password_reset' => true,
        'mail/auth/registration' => [
            'extra' => true,
            'body' => true,
        ],
        'mail/leads/newsletter' => [
            'title' => true,
        ],
        'common' => true,
    ],
    'es' => [
        'mail/auth/password_reset' => [
            'title' => true,
        ],
        'mail/auth/registration' => true,
        'mail/leads/newsletter' => true,
        'common' => [
            'extra' => true,
            'status.ooo' => true,
        ],
    ],
    'fr' => [
        'mail/auth/password_reset' => true,
        'mail/auth/registration' => [
            'title' => true,
        ],
        'mail/leads/newsletter' => [
            'title' => true,
            'extra' => true,
        ],
        'common' => true,
    ],
];
