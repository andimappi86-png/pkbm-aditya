<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Demo Environment Passwords
    |--------------------------------------------------------------------------
    |
    | These passwords are only used when APP_ENV=demo and the demo seeders run.
    | They are loaded from environment variables so they never appear in version
    | control. Each role gets a distinct strong password.
    |
    */

    'passwords' => [
        'operator' => env('DEMO_PASSWORD_OPERATOR'),
        'kepala-pkbm' => env('DEMO_PASSWORD_KEPALA'),
        'wali-kelas' => env('DEMO_PASSWORD_WALI'),
        'tutor' => env('DEMO_PASSWORD_TUTOR'),
        'peserta-didik' => env('DEMO_PASSWORD_PESERTA'),
        'ketua-yayasan' => env('DEMO_PASSWORD_YAYASAN'),
    ],

];