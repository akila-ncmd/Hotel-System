<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Room;

/**
 * Date-aware room availability.
 *
 * The system books a *room type*, not a specific room — `reservations.room_id` stays
 * NULL until a clerk assigns a physical room at check-in. That means `rooms.status`
 * is only a right-now flag and says nothing about whether a room type is already
 * spoken for on a future date. Availability therefore has to be derived by counting
 * overlapping reservations against the branch's inventory for that room type.
 */
class RoomAvailability
{
    /**
     * Reservation statuses that hold inventory. Cancelled, checked_out and no_show
     * reservations have released their room and must not count against capacity.
     */
    public const ACTIVE_STATUSES = ['pending', 'confirmed', 'checked_in'];

    /**
     * Total bookable rooms of a type at a branch. Rooms under maintenance are excluded;
     * an 'occupied' room still counts, because occupancy is a current-state flag and the
     * room becomes free again once that stay ends.
     */
    public static function capacity(int $branchId, int $roomTypeId): int
    {
        return Room::where('branch_id', $branchId)
            ->where('room_type_id', $roomTypeId)
            ->where('status', '!=', 'maintenance')
            ->count();
    }

    /**
     * How many rooms of this type are already committed for any part of the requested
     * window. Two ranges overlap when each starts before the other ends:
     *
     *     existing.check_in < requested.check_out  AND  existing.check_out > requested.check_in
     *
     * A NULL check_out_date is treated as an open-ended stay and always overlaps, which
     * is the safe reading — the schema permits NULL even though the app always computes one.
     *
     * @param  int|null  $excludeReservationId  Ignore this reservation (used when editing it).
     */
    public static function reserved(
        int $branchId,
        int $roomTypeId,
        $checkIn,
        $checkOut,
        ?int $excludeReservationId = null
    ): int {
        return Reservation::where('branch_id', $branchId)
            ->where('room_type_id', $roomTypeId)
            ->whereIn('status', self::ACTIVE_STATUSES)
            ->where('check_in_date', '<', $checkOut)
            ->where(function ($query) use ($checkIn) {
                $query->whereNull('check_out_date')
                      ->orWhere('check_out_date', '>', $checkIn);
            })
            ->when($excludeReservationId, function ($query) use ($excludeReservationId) {
                $query->where('id', '!=', $excludeReservationId);
            })
            ->count();
    }

    /**
     * Rooms of this type still free across the whole requested window.
     */
    public static function remaining(
        int $branchId,
        int $roomTypeId,
        $checkIn,
        $checkOut,
        ?int $excludeReservationId = null
    ): int {
        $remaining = self::capacity($branchId, $roomTypeId)
            - self::reserved($branchId, $roomTypeId, $checkIn, $checkOut, $excludeReservationId);

        return max(0, $remaining);
    }

    /**
     * Whether $quantity rooms of this type can be booked for the whole window.
     */
    public static function hasCapacityFor(
        int $branchId,
        int $roomTypeId,
        $checkIn,
        $checkOut,
        int $quantity = 1,
        ?int $excludeReservationId = null
    ): bool {
        return self::remaining($branchId, $roomTypeId, $checkIn, $checkOut, $excludeReservationId) >= $quantity;
    }
}
