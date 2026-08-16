<?php

use App\Support\CardGuarantee;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Reduces every stored card number to a masked guarantee.
 *
 * reservations.credit_card_details held raw PANs in plaintext. The application
 * now only ever writes the masked form (see App\Support\CardGuarantee), and
 * this migration brings existing rows into line.
 *
 * This is deliberately irreversible: down() cannot restore digits that this
 * migration is designed to destroy, and should not pretend otherwise. Any raw
 * numbers that existed before this ran must be treated as already exposed.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('reservations')
            ->whereNotNull('credit_card_details')
            ->where('credit_card_details', '!=', '')
            ->orderBy('id')
            ->chunkById(200, function ($reservations) {
                foreach ($reservations as $reservation) {
                    $value = $reservation->credit_card_details;

                    // Already masked by an earlier run — leave it alone.
                    if (str_contains($value, '*')) {
                        continue;
                    }

                    DB::table('reservations')
                        ->where('id', $reservation->id)
                        ->update([
                            'credit_card_details' => CardGuarantee::mask($value),
                        ]);
                }
            });
    }

    public function down(): void
    {
        // Intentionally a no-op. The card numbers this migration masked are
        // gone; there is nothing to roll back to.
    }
};
