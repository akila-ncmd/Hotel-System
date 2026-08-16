<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;
use App\Models\User;
use App\Models\RoomType;
use App\Models\Room;
use App\Models\TravelAgency;
use App\Models\Reservation;
use App\Models\Billing;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Branches
        $branches = [
            ['name' => 'Colombo Branch', 'address' => 'Galle Road, Colombo', 'contact_number' => '0112345678'],
            ['name' => 'Kandy Branch', 'address' => 'Peradeniya Road, Kandy', 'contact_number' => '0812345678'],
        ];
        foreach ($branches as $branch) {
            Branch::firstOrCreate(['name' => $branch['name']], $branch);
        }

        // Users
        $users = [
            [
                'name' => 'Customer One',
                'email' => 'customer@hotel.com',
                'password' => bcrypt('password'),
                'role' => 'customer',
                'nationality' => 'Sri Lankan',
                'contact_number' => '0771234567',
            ],
            [
                'name' => 'Clerk Colombo',
                'email' => 'clerk@hotel.com',
                'password' => bcrypt('password'),
                'role' => 'clerk',
                'branch_id' => 1,
                'nationality' => 'Sri Lankan',
                'contact_number' => '0771234568',
            ],
            [
                'name' => 'Manager Colombo',
                'email' => 'manager@hotel.com',
                'password' => bcrypt('password'),
                'role' => 'manager',
                'branch_id' => 1,
                'nationality' => 'Sri Lankan',
                'contact_number' => '0771234569',
            ],
            [
                'name' => 'Clerk Kandy',
                'email' => 'clerkKandy@hotel.com',
                'password' => bcrypt('password'),
                'role' => 'clerk',
                'branch_id' => 2,
                'nationality' => 'Sri Lankan',
                'contact_number' => '0771234234',
            ],
            [
                'name' => 'Manager Kandy',
                'email' => 'managerKandy@hotel.com',
                'password' => bcrypt('password'),
                'role' => 'manager',
                'branch_id' => 2,
                'nationality' => 'Sri Lankan',
                'contact_number' => '0711234568',
            ],
        ];
        foreach ($users as $user) {
            User::firstOrCreate(['email' => $user['email']], $user);
        }

        // Room Types
        $roomTypes = [
            ['name' => 'Single Room', 'description' => 'Cozy single room', 'price_per_night' => 50, 'max_occupants' => 1, 'is_suite' => false],
            ['name' => 'Double Room', 'description' => 'Spacious double room', 'price_per_night' => 80, 'max_occupants' => 2, 'is_suite' => false],
            ['name' => 'Residential Suite', 'description' => 'Luxury suite for extended stays', 'weekly_rate' => 1000, 'monthly_rate' => 3500, 'max_occupants' => 4, 'is_suite' => true],
        ];
        foreach ($roomTypes as $type) {
            RoomType::firstOrCreate(['name' => $type['name']], $type);
        }

        // Rooms for Each Branch
        $rooms = [];


        foreach ([1, 2] as $branch_id) { // Colombo (1) and Kandy (2)
            // Single Rooms (30)
            for ($floor = 1; $floor <= 3; $floor++) {
                for ($room = 1; $room <= 10; $room++) {
                    $room_number = sprintf("%d%02d", $floor, $room); // e.g., 101, 102, ..., 310
                    $rooms[] = [
                        'branch_id' => $branch_id,
                        'room_type_id' => 1, // Single Room
                        'room_number' => $room_number,
                        'status' => 'available',
                    ];
                }
            }

            // Double Rooms (20)
            for ($floor = 4; $floor <= 5; $floor++) {
                for ($room = 1; $room <= 10; $room++) {
                    $room_number = sprintf("%d%02d", $floor, $room); // e.g., 401, 402, ..., 510
                    $rooms[] = [
                        'branch_id' => $branch_id,
                        'room_type_id' => 2, // Double Room
                        'room_number' => $room_number,
                        'status' => 'available',
                    ];
                }
            }

            // Residential Suites (3)
            for ($room = 1; $room <= 3; $room++) {
                $room_number = "6{$room}"; // e.g., 601, 602, 603
                $rooms[] = [
                    'branch_id' => $branch_id,
                    'room_type_id' => 3, // Residential Suite
                    'room_number' => $room_number,
                    'status' => 'available',
                ];
            }
        }

        foreach ($rooms as $room) {
            Room::firstOrCreate(
                ['room_number' => $room['room_number'], 'branch_id' => $room['branch_id']],
                $room
            );
        }

        // Travel Agencies
        TravelAgency::firstOrCreate(
            ['name' => 'TravelEasy'],
            [
                'name' => 'TravelEasy',
                'contact_email' => 'contact@traveleasy.com',
                'contact_number' => '0119876543',
                'is_verified' => true,
            ]
        );

        $this->seedFrontDeskDay();
    }

    /**
     * Give the clerk's front desk something to show on the day it is run:
     * two arrivals due today, one departure due today, and one in-house guest
     * staying on. Without this, a freshly seeded database renders three empty
     * tabs and the screen looks broken rather than quiet.
     */
    private function seedFrontDeskDay(): void
    {
        $branch = Branch::where('name', 'Colombo Branch')->first();
        $customer = User::where('email', 'customer@hotel.com')->first();
        $roomType = RoomType::where('name', 'Single Room')->first();

        if (! $branch || ! $customer || ! $roomType) {
            return;
        }

        $today = Carbon::today();

        // Two arrivals due today — one guaranteed by a card, one not. The
        // unguaranteed one is what the 19:00 auto-cancel will pick up.
        foreach ([
            ['**** **** **** 4242 (exp 04/29)', 3],
            [null, 2],
        ] as $index => [$guarantee, $nights]) {
            Reservation::firstOrCreate(
                [
                    'user_id' => $customer->id,
                    'branch_id' => $branch->id,
                    'check_in_date' => $today,
                    'room_type_id' => $roomType->id,
                    'number_of_occupants' => $index + 1,
                ],
                [
                    'check_out_date' => $today->copy()->addDays($nights),
                    'status' => 'pending',
                    'credit_card_details' => $guarantee,
                ]
            );
        }

        // One in-house guest departing today, and one staying on. Both need a
        // physical room, since a checked-in reservation always has one.
        $rooms = Room::where('branch_id', $branch->id)
            ->where('room_type_id', $roomType->id)
            ->orderBy('room_number')
            ->take(2)
            ->get();

        foreach ([
            [0, $today->copy(), 'departing today'],
            [1, $today->copy()->addDays(2), 'staying on'],
        ] as [$offset, $checkOut, $_label]) {
            if (! isset($rooms[$offset])) {
                continue;
            }

            $reservation = Reservation::firstOrCreate(
                [
                    'user_id' => $customer->id,
                    'branch_id' => $branch->id,
                    'room_id' => $rooms[$offset]->id,
                    'status' => 'checked_in',
                ],
                [
                    'room_type_id' => $roomType->id,
                    'check_in_date' => $today->copy()->subDays(2),
                    'check_out_date' => $checkOut,
                    'number_of_occupants' => 1,
                    'credit_card_details' => '**** **** **** 4242 (exp 04/29)',
                ]
            );

            $rooms[$offset]->update(['status' => 'occupied']);

            Billing::firstOrCreate(
                ['reservation_id' => $reservation->id],
                [
                    'user_id' => $customer->id,
                    'branch_id' => $branch->id,
                    'total_amount' => $roomType->price_per_night * 2,
                    'payment_status' => 'pending',
                ]
            );
        }
    }
}