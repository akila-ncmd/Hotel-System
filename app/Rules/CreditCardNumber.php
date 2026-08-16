<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validates a card number's shape and Luhn checksum.
 *
 * This only proves the number is well-formed — it is deliberately the last
 * thing that ever sees the full PAN. App\Support\CardGuarantee immediately
 * reduces it to a masked value and nothing downstream persists the digits.
 *
 * Values that are already masked (they contain '*') pass straight through, so
 * an edit form can resubmit the stored guarantee without re-entering the card.
 */
class CreditCardNumber implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        // Already-masked guarantee resubmitted from an edit form.
        if (str_contains((string) $value, '*')) {
            return;
        }

        $digits = preg_replace('/\D/', '', (string) $value);

        if (strlen($digits) < 13 || strlen($digits) > 19) {
            $fail('The card number must be between 13 and 19 digits.');
            return;
        }

        if (! $this->passesLuhn($digits)) {
            $fail('The card number is not valid.');
        }
    }

    /**
     * Standard Luhn mod-10 checksum: double every second digit from the right,
     * subtracting 9 from any result above 9, and require the sum to divide by 10.
     */
    private function passesLuhn(string $digits): bool
    {
        $sum = 0;
        $double = false;

        for ($i = strlen($digits) - 1; $i >= 0; $i--) {
            $digit = (int) $digits[$i];

            if ($double) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
            $double = ! $double;
        }

        return $sum % 10 === 0;
    }
}
