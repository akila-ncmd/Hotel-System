<?php

namespace Tests\Feature;

use App\Console\Commands\ProcessNoShowsAndReport;
use App\Models\Billing;
use App\Models\Branch;
use App\Models\Report;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Manager reporting and the nightly close.
 *
 * The 19:00 command is not a real night audit (see docs/gap-analysis.md §9),
 * but it does bill no-shows and write one report row per branch — which is what
 * these tests pin down.
 */
class ManagerOperationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Storage::fake('local');
    }

    /**
     * Yesterday's unfulfilled reservations are billed at the full room rate and
     * flipped to no_show.
     */
    public function test_no_show_billing_and_status_change(): void
    {
        $branch = Branch::factory()->create();
        $roomType = RoomType::factory()->create(['price_per_night' => 100]);

        Room::factory()->count(2)->create([
            'branch_id' => $branch->id,
            'room_type_id' => $roomType->id,
        ]);

        $reservation = Reservation::factory()->create([
            'branch_id' => $branch->id,
            'room_type_id' => $roomType->id,
            'status' => 'pending',
            'check_in_date' => Carbon::yesterday(),
            'check_out_date' => Carbon::today(),
        ]);

        $this->artisan(ProcessNoShowsAndReport::class)->assertSuccessful();

        $this->assertSame('no_show', $reservation->fresh()->status);

        // A folio is raised for the unfulfilled stay. Note the billing is left
        // 'pending', not 'no_show' — billings.payment_status has a 'no_show'
        // value that nothing ever writes. Asserted as-is rather than as it
        // arguably should be; see docs/gap-analysis.md §5.
        $this->assertDatabaseHas('billings', [
            'reservation_id' => $reservation->id,
            'payment_status' => 'pending',
        ]);
        $this->assertGreaterThan(0, $reservation->fresh()->billing->total_amount);
    }

    /**
     * The command writes one report row per branch — it previously wrote
     * branch_id => null into a NOT NULL column and failed silently.
     */
    public function test_nightly_command_writes_one_report_per_branch(): void
    {
        $branchA = Branch::factory()->create();
        $branchB = Branch::factory()->create();
        $roomType = RoomType::factory()->create(['price_per_night' => 100]);

        foreach ([$branchA, $branchB] as $branch) {
            Room::factory()->count(2)->create([
                'branch_id' => $branch->id,
                'room_type_id' => $roomType->id,
            ]);
        }

        $this->artisan(ProcessNoShowsAndReport::class)->assertSuccessful();

        $this->assertDatabaseHas('reports', [
            'branch_id' => $branchA->id,
            'report_date' => Carbon::yesterday()->toDateString(),
        ]);
        $this->assertDatabaseHas('reports', [
            'branch_id' => $branchB->id,
            'report_date' => Carbon::yesterday()->toDateString(),
        ]);
    }

    /**
     * Re-running the close corrects the existing row rather than duplicating it.
     */
    public function test_nightly_command_is_idempotent_for_a_given_date(): void
    {
        $branch = Branch::factory()->create();
        $roomType = RoomType::factory()->create(['price_per_night' => 100]);

        Room::factory()->count(2)->create([
            'branch_id' => $branch->id,
            'room_type_id' => $roomType->id,
        ]);

        $this->artisan(ProcessNoShowsAndReport::class)->assertSuccessful();
        $this->artisan(ProcessNoShowsAndReport::class)->assertSuccessful();

        $this->assertSame(1, Report::where('branch_id', $branch->id)
            ->where('report_date', Carbon::yesterday()->toDateString())
            ->count());
    }

    public function test_manager_dashboard_loads(): void
    {
        $branch = Branch::factory()->create();
        $manager = User::factory()->manager($branch)->create();

        $this->actingAs($manager)
            ->get(route('manager.dashboard', ['branch_id' => $branch->id]))
            ->assertStatus(200);
    }

    public function test_manager_can_download_a_report_as_pdf(): void
    {
        $branch = Branch::factory()->create();
        $manager = User::factory()->manager($branch)->create();

        $report = Report::factory()->create([
            'branch_id' => $branch->id,
            'report_date' => Carbon::yesterday()->toDateString(),
        ]);

        $response = $this->actingAs($manager)->get(route('manager.download-report', [
            'date' => $report->report_date,
            'format' => 'pdf',
        ]));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_manager_can_download_a_report_as_excel(): void
    {
        $branch = Branch::factory()->create();
        $manager = User::factory()->manager($branch)->create();

        $report = Report::factory()->create([
            'branch_id' => $branch->id,
            'report_date' => Carbon::yesterday()->toDateString(),
        ]);

        $this->actingAs($manager)
            ->get(route('manager.download-report', [
                'date' => $report->report_date,
                'format' => 'excel',
            ]))
            ->assertStatus(200);
    }

    public function test_occupancy_report_loads(): void
    {
        $branch = Branch::factory()->create();
        $manager = User::factory()->manager($branch)->create();
        $roomType = RoomType::factory()->create();

        Room::factory()->create([
            'branch_id' => $branch->id,
            'room_type_id' => $roomType->id,
        ]);

        Reservation::factory()->create([
            'branch_id' => $branch->id,
            'room_type_id' => $roomType->id,
        ]);

        $this->actingAs($manager)
            ->get(route('manager.occupancy-report', ['branch_id' => $branch->id]))
            ->assertStatus(200);
    }

    public function test_revenue_report_loads(): void
    {
        $branch = Branch::factory()->create();
        $manager = User::factory()->manager($branch)->create();

        Report::factory()->create([
            'branch_id' => $branch->id,
            'total_revenue' => 1234.50,
        ]);

        $this->actingAs($manager)
            ->get(route('manager.revenue-report', ['branch_id' => $branch->id]))
            ->assertStatus(200);
    }

    public function test_calendar_view_loads(): void
    {
        $branch = Branch::factory()->create();
        $manager = User::factory()->manager($branch)->create();

        $this->actingAs($manager)
            ->get(route('manager.calendar-view', ['branch_id' => $branch->id]))
            ->assertStatus(200);
    }

    /**
     * A manager is pinned to their own branch and must not see another's.
     */
    public function test_manager_cannot_download_another_branchs_report(): void
    {
        $ownBranch = Branch::factory()->create();
        $otherBranch = Branch::factory()->create();
        $manager = User::factory()->manager($ownBranch)->create();

        $report = Report::factory()->create([
            'branch_id' => $otherBranch->id,
            'report_date' => Carbon::yesterday()->toDateString(),
        ]);

        $this->actingAs($manager)
            ->get(route('manager.download-report', [
                'date' => $report->report_date,
                'format' => 'pdf',
            ]))
            ->assertNotFound();
    }
}
