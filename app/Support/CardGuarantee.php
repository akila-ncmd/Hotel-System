<?php

namespace App\Support;

use App\Rules\CreditCardNumber;
use Illuminate\Http\Request;

/**
 * Turns a card number + expiry into a storable guarantee.
 *
 * reservations.credit_card_details previously held the raw PAN in plaintext.
 * It now holds only a masked reference — last four digits and expiry — which is
 * all the system ever actually used it for:
 *
 *   - the 19:00 auto-cancel checks whether a guarantee exists at all
 *     (App\Console\Commands\CancelNoCreditReservations), and
 *   - the front desk shows the guest which card is on file.
 *
 * The full number is validated (see App\Rules\CreditCardNumber) and then
 * discarded. It is never written to the database or to the log.
 */
class CardGuarantee
{
    /** Existing column and form field holding the (now masked) guarantee. */
    public const NUMBER_FIELD = 'credit_card_details';

    /** Companion expiry field, MM/YY. */
    public const EXPIRY_FIELD = 'card_expiry';

    /**
     * Validation rules for the two card inputs.
     *
     * Merge into a controller's existing $rules array. Pass $requiredIf to
     * reproduce a conditional rule such as check-in's "required if arriving
     * today"; leave it null for the ordinary optional case.
     */
    public static function rules(?string $requiredIf = null): array
    {
        $presence = $requiredIf ? $requiredIf . '|nullable' : 'nullable';

        return [
            self::NUMBER_FIELD => [$presence, 'string', new CreditCardNumber()],
            self::EXPIRY_FIELD => [
                'nullable',
                'string',
                'regex:/^(0[1-9]|1[0-2])\/\d{2}$/',
                // required_with so a card can never be stored without its expiry
                'required_with:' . self::NUMBER_FIELD,
            ],
        ];
    }

    /**
     * Read the card inputs off a request and return the value to persist.
     *
     * Returns null when no card was supplied, which is what the 19:00
     * auto-cancel treats as "no guarantee".
     */
    public static function fromRequest(Request $request): ?string
    {
        return self::mask(
            $request->input(self::NUMBER_FIELD),
            $request->input(self::EXPIRY_FIELD)
        );
    }

    /**
     * Reduce a card number to its masked form, e.g. "**** **** **** 4242 (exp 04/29)".
     *
     * A value that is already masked is returned unchanged so that resubmitting
     * an edit form does not destroy the stored guarantee.
     */
    public static function mask(?string $number, ?string $expiry = null): ?string
    {
        if ($number === null || trim($number) === '') {
            return null;
        }

        // Edit form resubmitted the stored guarantee rather than a new card.
        if (str_contains($number, '*')) {
            return $number;
        }

        $digits = preg_replace('/\D/', '', $number);

        if (strlen($digits) < 4) {
            return null;
        }

        $masked = '**** **** **** ' . substr($digits, -4);

        return $expiry ? $masked . ' (exp ' . $expiry . ')' : $masked;
    }
}
