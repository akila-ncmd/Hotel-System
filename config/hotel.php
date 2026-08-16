<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    |
    | Every monetary value in the system is stored as a bare decimal(10,2) with
    | no currency information attached. These settings are the single place the
    | chain's trading currency is declared, and App\Support\Money is the only
    | thing that should read them.
    |
    */

    'currency' => env('HOTEL_CURRENCY', 'USD'),

    'currency_symbol' => env('HOTEL_CURRENCY_SYMBOL', '$'),

    // Digits shown after the decimal point when formatting money for display.
    'currency_decimals' => 2,

    /*
    |--------------------------------------------------------------------------
    | Contact
    |--------------------------------------------------------------------------
    |
    | The chain's public contact points. Read by the footer and the legal pages
    | so a number or address is written down once. `hotline_tel` is the E.164
    | form for the `tel:` href; `hotline` is what a human reads.
    |
    */

    'hotline'       => env('HOTEL_HOTLINE', '+94 11 234 5678'),
    'hotline_tel'   => env('HOTEL_HOTLINE_TEL', '+94112345678'),
    'whatsapp'      => env('HOTEL_WHATSAPP', '+94112345678'),
    'email'         => env('HOTEL_EMAIL', 'stay@diamondshine.test'),
    'email_legal'   => env('HOTEL_EMAIL_LEGAL', 'privacy@diamondshine.test'),
    'address'       => env('HOTEL_ADDRESS', '221 Galle Road, Colombo 03, Sri Lanka'),

    /*
    |--------------------------------------------------------------------------
    | Social
    |--------------------------------------------------------------------------
    |
    | Rendered as an icon row in the footer. A blank URL removes that link
    | entirely rather than rendering a dead icon, so a chain without an X
    | account simply sets HOTEL_SOCIAL_X= and the row closes up.
    |
    */

    // Icons are Font Awesome brand classes, not Bootstrap Icons: the CDN pins
    // bootstrap-icons 1.10.0, which predates `bi-twitter-x`.
    'social' => [
        'facebook'  => ['url' => env('HOTEL_SOCIAL_FACEBOOK', 'https://facebook.com/diamondshinehotels'),   'label' => 'Facebook',  'icon' => 'fa-brands fa-facebook-f'],
        'instagram' => ['url' => env('HOTEL_SOCIAL_INSTAGRAM', 'https://instagram.com/diamondshinehotels'), 'label' => 'Instagram', 'icon' => 'fa-brands fa-instagram'],
        'x'         => ['url' => env('HOTEL_SOCIAL_X', 'https://x.com/diamondshine'),                       'label' => 'X',         'icon' => 'fa-brands fa-x-twitter'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Stay policy
    |--------------------------------------------------------------------------
    |
    | Display-only for now: the booking engine does not yet enforce arrival or
    | departure times (see docs/gap-analysis.md). These are the times published
    | to guests, and the same values the cancellation policy page quotes.
    |
    */

    'check_in_time'       => env('HOTEL_CHECK_IN_TIME', '2:00 PM'),
    'check_out_time'      => env('HOTEL_CHECK_OUT_TIME', '11:00 AM'),
    'cancellation_hours'  => (int) env('HOTEL_CANCELLATION_HOURS', 48),

    // Company details required on a commercial site's legal pages.
    'legal_entity'     => env('HOTEL_LEGAL_ENTITY', 'Diamond Shine Hotels (Pvt) Ltd'),
    'registration_no'  => env('HOTEL_REGISTRATION_NO', 'PV 00123456'),

];
