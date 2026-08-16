<?php

namespace App\Support;

/**
 * The single place money is turned into text.
 *
 * Amounts are stored as bare decimal(10,2) columns with no currency attached,
 * so the currency lives in config/hotel.php and is applied here on the way out.
 * Nothing else in the codebase should hardcode a currency symbol.
 */
class Money
{
    /**
     * Format an amount for display, e.g. "$1,234.50".
     *
     * Accepts null so callers can hand over an optional column without a
     * null-coalesce at every call site; null is treated as zero.
     */
    public static function format($amount, ?int $decimals = null): string
    {
        $decimals = $decimals ?? config('hotel.currency_decimals', 2);

        return config('hotel.currency_symbol', '$')
            . number_format((float) ($amount ?? 0), $decimals);
    }

    /**
     * Format without the symbol, for table columns that carry the currency in
     * their header and for spreadsheet cells.
     */
    public static function plain($amount, ?int $decimals = null): string
    {
        $decimals = $decimals ?? config('hotel.currency_decimals', 2);

        return number_format((float) ($amount ?? 0), $decimals);
    }

    /**
     * The ISO code, for report headers and export column labels.
     */
    public static function code(): string
    {
        return config('hotel.currency', 'USD');
    }

    /**
     * The bare symbol, for input group prefixes and JS that builds its own
     * strings client-side.
     */
    public static function symbol(): string
    {
        return config('hotel.currency_symbol', '$');
    }
}
