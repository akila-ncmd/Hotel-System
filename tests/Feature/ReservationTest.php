<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Branch;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Reservation;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class ReservationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->artisan('migrate', ['--database' => 'mysql']);
    }

    public function test_customer_can_create_room_reservation()
    {
        $user = User::factory()->create(['role' => 'customer']);
        $branch = Branch::factory()->create();
        $roomType = RoomType::factory()->create(['branch_id' => $branch->id]);
        $room = Room::factory()->create(['branch_id' => $branch->id, 'room_type_id' => $roomType->id, 'status' => 'available']);

        $this->actingAs($user);

        $response = $this->post('/reservations', [
            'branch_id' => $branch->id,
            'room_type_id' => $roomType->id,
            'check_in_date' => Carbon::today()->addDay()->format('Y-m-d'),
            'check_out_date' => Carbon::today()->addDays(2)->format('Y-m-d'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('reservations', [
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'room_type_id' => $roomType->id,
        ]);

        Mail::assertSent(\App\Mail\ReservationConfirmation::class);
    }

    public function test_customer_can_create_suite_reservation()
    {
        $user = User::factory()->create(['role' => 'customer']);
        $branch = Branch::factory()->create();
        $roomType = RoomType::factory()->create(['branch_id' => $branch->id, 'is_suite' => true]);
        $room = Room::factory()->create(['branch_id' => $branch->id, 'room_type_id' => $roomType->id, 'status' => 'available']);

        $this->actingAs($user);

        $response = $this->post('/reservations', [
            'branch_id' => $branch->id,
            'room_type_id' => $roomType->id,
            'check_in_date' => Carbon::today()->addDay()->format('Y-m-d'),
            'check_out_date' => Carbon::today()->addDays(2)->format('Y-m-d'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('reservations', [
            'user_id' => $user->id,
            'room_type_id' => $roomType->id,
            'is_suite' => true,
        ]);

        Mail::assertSent(\App\Mail\ReservationConfirmation::class);
    }

    public function test_reservation_management_dashboard()
    {
        $user = User::factory()->create(['role' => 'customer']);
        $reservation = Reservation::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $response = $this->get('/reservations');

        $response->assertStatus(200);
        $response->assertSee($reservation->check_in_date);
    }

    public function test_customer_can_edit_reservation()
    {
        $user = User::factory()->create(['role' => 'customer']);
        $reservation = Reservation::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $newDate = Carbon::parse($reservation->check_in_date)->addDay()->format('Y-m-d');
        $response = $this->put("/reservations/{$reservation->id}", [
            'check_in_date' => $newDate,
            'check_out_date' => Carbon::parse($reservation->check_out_date)->addDay()->format('Y-m-d'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'check_in_date' => $newDate,
        ]);

        Mail::assertSent(\App\Mail\ReservationConfirmation::class);
    }

    public function test_clerk_can_create_reservation()
    {
        $clerk = User::factory()->create(['role' => 'clerk', 'branch_id' => Branch::factory()->create()->id]);
        $roomType = RoomType::factory()->create(['branch_id' => $clerk->branch_id]);
        $room = Room::factory()->create(['branch_id' => $clerk->branch_id, 'room_type_id' => $roomType->id, 'status' => 'available']);

        $this->actingAs($clerk);

        $response = $this->post('/clerk/reservations', [
            'user_id' => User::factory()->create(['role' => 'customer'])->id,
            'branch_id' => $clerk->branch_id,
            'room_type_id' => $roomType->id,
            'check_in_date' => Carbon::today()->addDay()->format('Y-m-d'),
            'check_out_date' => Carbon::today()->addDays(2)->format('Y-m-d'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('reservations', [
            'branch_id' => $clerk->branch_id,
            'room_type_id' => $roomType->id,
        ]);

        Mail::assertSent(\App\Mail\ReservationConfirmation::class);
    }

    public function test_customer_can_cancel_reservation()
    {
        $user = User::factory()->create(['role' => 'customer']);
        $reservation = Reservation::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $response = $this->delete("/reservations/{$reservation->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('reservations', ['id' => $reservation->id]);

        Mail::assertSent(\App\Mail\ReservationConfirmation::class);
    }

    public function test_auto_cancel_reservations_without_payment()
    {
        $reservation = Reservation::factory()->create([
            'payment_status' => null,
            'created_at' => Carbon::today()->subHours(12),
        ]);

        $this->artisan('schedule:run');

        $this->assertDatabaseMissing('reservations', ['id' => $reservation->id]);
    }
}