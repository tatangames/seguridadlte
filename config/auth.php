<?php

return [

    'defaults' => [
        // Puedes dejar 'web' si quieres, pero pongo 'admin' para que ese sea el guard por defecto.
        'guard' => 'admin',
        'passwords' => 'admins',
    ],

    'guards' => [
        // Mantén 'web' si lo usas en otras partes; aquí lo apunto al mismo provider 'admin'.
        'web' => [
            'driver' => 'session',
            'provider' => 'admin',
        ],

        // ✅
        'admin' => [
            'driver' => 'session',
            'provider' => 'admin',
        ],
    ],

    'providers' => [
        'admin' => [
            'driver' => 'eloquent',
            'model' => App\Models\Usuario::class,
        ],
        // Si más adelante usas un modelo User, lo agregas aquí como 'users'
    ],

    'passwords' => [
        // ✅ Reset para administradores (opcional si no lo usas)
        'admins' => [
            'provider' => 'admin',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,
];
