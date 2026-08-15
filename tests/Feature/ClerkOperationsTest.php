<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Branch;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Reservation;
use App\Models\Billing;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class ClerkOperationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->artisan('migrate', ['--database' => 'mysql']);
    }

    public function test_clerk_dashboard_loads()
    {
        $clerk = User::factory()->create(['role' => 'clerk', 'branch_id' => Branch::factory()->create()->id]);
        $this->actingAs($clerk);

        $response = $this->get(route('clerk.dashboard', ['branch_id' => $clerk->branch_id]));

        $response->assertStatus(200);
    }

    public function test_admin_dashboard_loads()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->get(route('admin.dashboard'));

        $response->assertStatus(200);
    }

    public function test_clerk_registration_by_admin()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $branch = Branch::factory()->create();

        $this->actingAs($admin);

        $response = $this->post('/staff/register', [
            'name' => 'New Clerk',
            'email' => 'clerk@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'clerk',
            'branch_id' => $branch->id,
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertDatabaseHas('users', [
            'email' => 'clerk@example.com',
            'role' => 'clerk',
            'branch_id' => $branch->id,
        ]);

        Mail::assertSent(\App\Mail\RegistrationConfirmation::class);
    }

    public function test_clerk_can_check_in_customer()
    {
        $clerk = User::factory()->create(['role' => 'clerk', 'branch_id' => Branch::factory()->create()->id]);
        $reservation = Reservation::factory()->create(['branch_id' => $clerk->branch_id, 'status' => 'confirmed']);
        $room = Room::factory()->create(['branch_id' => $clerk->branch_id, 'status' => 'available']);

        $this->actingAs($clerk);

        $response = $this->post("/reservations/{$reservation->id}/check-in", [
            'room_id' => $room->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'checked_in',
            'room_id' => $room->id,
        ]);
    }

    public function test_clerk_can_check_out_with_billing()
    {
        $clerk = User::factory()->create(['role' => 'clerk', 'branch_id' => Branch::factory()->create()->id]);
        $reservation = Reservation::factory()->create(['branch_id' => $clerk->branch_id, 'status' => 'checked_in']);

        $this->actingAs($clerk);

        $response = $this->post("/reservations/{$reservation->id}/check-out", [
            'total_amount' => 100.00,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'checked_out',
        ]);
        $this->assertDatabaseHas('billings', [
            'reservation_id' => $reservation->id,
            'total_amount' => 100.00,
        ]);

        Mail::assertSent(\App\Mail\PaymentConfirmation::class);
    }

    public function test_clerk_can_edit_check_out()
    {
        $clerk = User::factory()->create(['role' => 'clerk', 'branch_id' => Branch::factory()->create()->id]);
        $reservation = Reservation::factory()->create(['branch_id' => $clerk->branch_id, 'status' => 'checked_out']);
        $billing = Billing::factory()->create(['reservation_id' => $reservation->id, 'total_amount' => 100.00]);

        $this->actingAs($clerk);

        $response = $this->put("/reservations/{$reservation->id}/check-out", [
            'total_amount' => 150.00,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('billings', [
            'reservation_id' => $reservation->id,
            'total_amount' => 150.00,
        ]);

        Mail::assertSent(\App\Mail\PaymentConfirmation::class);
    }

    public function test_room_availability_table()
    {
        $clerk = User::factory()->create(['role' => 'clerk', 'branch_id' => Branch::factory()->create()->id]);
        $room = Room::factory()->create(['branch_id' => $clerk->branch_id, 'status' => 'available']);

        $this->actingAs($clerk);

        $response = $this->get(route('clerk.room-availability', ['branch_id' => $clerk->branch_id]));

        $response->assertStatus(200);
        $response->assertSee($room->room_number);
    }

    public function test_travel_agency_block_booking()
    {
        $clerk = User::factory()->create(['role' => 'clerk', 'branch_id' => Branch::factory()->create()->id]);
        $roomType = RoomType::factory()->create(['branch_id' => $clerk->branch_id]);
        $rooms = Room::factory()->count(5)->create(['branch_id' => $clerk->branch_id, 'room_type_id' => $roomType->id, 'status' => 'available']);

        $this->actingAs($clerk);

        $response = $this->post('/travel-agency/bookings', [
            'room_type_id' => $roomType->id,
            'quantity' => 3,
            'check_in_date' => Carbon::today()->addDay()->format('Y-m-d'),
            'check_out_date' => Carbon::today()->addDays(2)->format('Y-m-d'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('reservations', 3);
    }
}