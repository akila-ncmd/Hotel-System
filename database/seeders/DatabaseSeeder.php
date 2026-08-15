<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;
use App\Models\User;
use App\Models\RoomType;
use App\Models\Room;
use App\Models\TravelAgency;

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
    }
}