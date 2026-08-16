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

];
