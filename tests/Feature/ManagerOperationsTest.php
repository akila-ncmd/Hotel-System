<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Branch;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Reservation;
use App\Models\Report;
use App\Models\Billing;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class ManagerOperationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Storage::fake('local');
        $this->artisan('migrate', ['--database' => 'mysql']);
    }

    public function test_no_show_billing_at_7pm()
    {
        $reservation = Reservation::factory()->create([
            'status' => 'confirmed',
            'check_in_date' => Carbon::yesterday(),
            'branch_id' => Branch::factory()->create()->id,
        ]);

        $this->artisan('schedule:run');

        $this->assertDatabaseHas('billings', [
            'reservation_id' => $reservation->id,
            'payment_status' => 'no_show',
        ]);
    }

    public function test_daily_report_generation_at_7pm()
    {
        $branch = Branch::factory()->create();
        $reservation = Reservation::factory()->create([
            'branch_id' => $branch->id,
            'check_in_date' => Carbon::yesterday(),
            'status' => 'checked_in',
        ]);
        Billing::factory()->create([
            'reservation_id' => $reservation->id,
            'total_amount' => 100.00,
        ]);

        $this->artisan('schedule:run');

        $this->assertDatabaseHas('reports', [
            'branch_id' => $branch->id,
            'report_date' => Carbon::yesterday()->format('Y-m-d'),
            'total_occupancy' => 1,
            'total_revenue' => 100.00,
        ]);

        Mail::assertSent(\App\Mail\ManagerReport::class);
    }

    public function test_pdf_report_generation()
    {
        $manager = User::factory()->create(['role' => 'manager', 'branch_id' => Branch::factory()->create()->id]);
        $report = Report::factory()->create(['branch_id' => $manager->branch_id]);

        $this->actingAs($manager);

        $response = $this->get(route('manager.download-report', ['date' => $report->report_date, 'format' => 'pdf']));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_manager_dashboard_loads()
    {
        $manager = User::factory()->create(['role' => 'manager', 'branch_id' => Branch::factory()->create()->id]);
        $this->actingAs($manager);

        $response = $this->get(route('manager.dashboard', ['branch_id' => $manager->branch_id]));

        $response->assertStatus(200);
    }

    public function test_occupancy_report_generation()
    {
        $manager = User::factory()->create(['role' => 'manager', 'branch_id' => Branch::factory()->create()->id]);
        $reservation = Reservation::factory()->create(['branch_id' => $manager->branch_id]);

        $this->actingAs($manager);

        $response = $this->get(route('manager.occupancy-report', [
            'date_range' => Carbon::today()->format('Y-m-d') . ' - ' . Carbon::today()->format('Y-m-d'),
        ]));

        $response->assertStatus(200);
        $response->assertSee($reservation->check_in_date);
    }

    public function test_revenue_report_generation()
    {
        $manager = User::factory()->create(['role' => 'manager', 'branch_id' => Branch::factory()->create()->id]);
        $reservation = Reservation::factory()->create(['branch_id' => $manager->branch_id]);
        Billing::factory()->create(['reservation_id' => $reservation->id, 'total_amount' => 100.00]);

        $this->actingAs($manager);

        $response = $this->get(route('manager.revenue-report', [
            'date_range' => Carbon::today()->format('Y-m-d') . ' - ' . Carbon::today()->format('Y-m-d'),
        ]));

        $response->assertStatus(200);
        $response->assertSee('100.00');
    }

    public function test_calendar_view()
    {
        $manager = User::factory()->create(['role' => 'manager', 'branch_id' => Branch::factory()->create()->id]);
        $room = Room::factory()->create(['branch_id' => $manager->branch_id]);

        $this->actingAs($manager);

        $response = $this->get(route('manager.calendar-view', ['branch_id' => $manager->branch_id]));

        $response->assertStatus(200);
        $response->assertSee($room->room_number);
    }
}