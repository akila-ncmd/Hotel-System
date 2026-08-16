<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * Authentication is hand-rolled on the Auth facade and is stricter than usual:
 * the user must supply email + password AND the matching role AND, for staff,
 * their own branch. Any mismatch forces a logout and counts against a 5-attempt
 * rate limit. These tests pin that behaviour down.
 */
class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    public function test_customer_registration_sends_confirmation_email(): void
    {
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'nationality' => 'Sri Lankan',
            // Registration requires an international format (+CC then 7-15 digits).
            'contact_number' => '+94771234567',
        ]);

        $response->assertRedirect(route('customer.reservations'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'role' => 'customer',
        ]);

        Mail::assertSent(\App\Mail\RegistrationConfirmation::class, function ($mail) {
            return $mail->hasTo('john@example.com');
        });
    }

    public function test_customer_login_with_correct_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'customer@example.com',
            'password' => Hash::make('password123'),
            'role' => 'customer',
        ]);

        $response = $this->post('/login', [
            'email' => 'customer@example.com',
            'password' => 'password123',
            'role' => 'customer',
        ]);

        $response->assertRedirect(route('customer.reservations'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_clerk_login_requires_matching_branch(): void
    {
        $branch = Branch::factory()->create();
        $user = User::factory()->clerk($branch)->create([
            'email' => 'clerk@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'clerk@example.com',
            'password' => 'password123',
            'role' => 'clerk',
            'branch_id' => $branch->id,
        ]);

        $response->assertRedirect(route('clerk.dashboard', ['branch_id' => $branch->id]));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_wrong_branch(): void
    {
        $branch = Branch::factory()->create();
        $otherBranch = Branch::factory()->create();

        User::factory()->clerk($branch)->create([
            'email' => 'clerk@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'clerk@example.com',
            'password' => 'password123',
            'role' => 'clerk',
            'branch_id' => $otherBranch->id,
        ]);

        $response->assertSessionHasErrors('branch_id');
        $this->assertGuest();
    }

    public function test_login_fails_with_mismatched_role(): void
    {
        User::factory()->create([
            'email' => 'customer@example.com',
            'password' => Hash::make('password123'),
            'role' => 'customer',
        ]);

        // Correct password, wrong role — the account is a customer, not a manager.
        $response = $this->post('/login', [
            'email' => 'customer@example.com',
            'password' => 'password123',
            'role' => 'manager',
            'branch_id' => Branch::factory()->create()->id,
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertGuest();
    }

    public function test_login_rate_limiting(): void
    {
        $email = 'invalid@example.com';
        RateLimiter::clear('login:' . $email . '|127.0.0.1');

        // Five failures are allowed; the sixth is blocked by the limiter.
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => $email,
                'password' => 'wrong',
                'role' => 'customer',
            ])->assertSessionHasErrors('email');
        }

        $response = $this->post('/login', [
            'email' => $email,
            'password' => 'wrong',
            'role' => 'customer',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString(
            'Too many login attempts',
            session('errors')->first('email')
        );
        $this->assertGuest();
    }

    public function test_logout_clears_session(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
        $this->assertFalse(session()->has('admin_selected_branch'));
    }

    public function test_non_admin_is_forbidden_from_admin_routes(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->get('/admin/dashboard')
            ->assertStatus(403);
    }

    public function test_admin_can_reach_admin_routes(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertStatus(200);
    }

    public function test_admin_bypasses_every_role_check(): void
    {
        // RoleMiddleware short-circuits for admins by design, so an admin can
        // reach a clerk-only route without holding the clerk role.
        $admin = User::factory()->admin()->create();
        $branch = Branch::factory()->create();

        $this->actingAs($admin)
            ->get(route('clerk.dashboard', ['branch_id' => $branch->id]))
            ->assertStatus(200);
    }

    public function test_user_profile_editing(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);

        $response = $this->actingAs($user)->put('/profile', [
            'name' => 'Updated Name',
            'email' => 'user@example.com',
            'contact_number' => '9876543210',
            'nationality' => 'Sri Lankan',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'contact_number' => '9876543210',
        ]);
    }
}
