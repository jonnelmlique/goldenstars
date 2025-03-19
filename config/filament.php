<?php

return [

    // ...existing code...

    'auth' => [
        'guard' => 'web',
        'pages' => [
            'login' => \App\Filament\Pages\Auth\Login::class,
        ],
    ],

    'default_login_field' => 'username',
    'login_fields' => ['username', 'email'],

    // ...existing code...

];