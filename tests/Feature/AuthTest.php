<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->artisan('migrate', ['--database' => 'mysql']);
    }

    public function test_customer_registration_sends_confirmation_email()
    {
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'nationality' => 'Sri Lankan',
            'contact_number' => '1234567890',
        ]);

        $response->assertRedirect(route('customer.dashboard'));
        $response->assertSessionHas('success', 'Registration successful! A confirmation email has been sent.');

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'role' => 'customer',
        ]);

        Mail::assertSent(\App\Mail\RegistrationConfirmation::class, function ($mail) {
            return $mail->hasTo('john@example.com');
        });
    }

    public function test_customer_login_with_correct_credentials()
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

        $response->assertRedirect(route('customer.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_clerk_login_with_branch_selection()
    {
        $branch = Branch::factory()->create(['name' => 'Colombo']);
        $user = User::factory()->create([
            'email' => 'clerk@example.com',
            'password' => Hash::make('password123'),
            'role' => 'clerk',
            'branch_id' => $branch->id,
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

    public function test_login_fails_with_invalid_branch()
    {
        $branch = Branch::factory()->create(['name' => 'Colombo']);
        $user = User::factory()->create([
            'email' => 'clerk@example.com',
            'password' => Hash::make('password123'),
            'role' => 'clerk',
            'branch_id' => $branch->id,
        ]);

        $response = $this->post('/login', [
            'email' => 'clerk@example.com',
            'password' => 'password123',
            'role' => 'clerk',
            'branch_id' => $branch->id + 1,
        ]);

        $response->assertSessionHasErrors(['branch_id' => 'Invalid branch selection for your account.']);
        $this->assertGuest();
    }

    public function test_login_rate_limiting()
    {
        $key = 'login:invalid@example.com|' . request()->ip();
        RateLimiter::clear($key);

        for ($i = 0; $i < 5; $i++) {
            $response = $this->post('/login', [
                'email' => 'invalid@example.com',
                'password' => 'wrong',
                'role' => 'customer',
            ]);
            $response->assertSessionHasErrors(['email']);
        }

        $response = $this->post('/login', [
            'email' => 'invalid@example.com',
            'password' => 'wrong',
            'role' => 'customer',
        ]);

        $response->assertSessionHasErrors(['email' => fn ($message) => str_contains($message, 'Too many login attempts')]);
        $this->assertGuest();
    }

    public function test_logout_clears_session()
    {
        $user = User::factory()->create(['role' => 'customer']);
        $this->actingAs($user);

        $response = $this->post('/logout');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
        $this->assertFalse(Session::has('admin_selected_branch'));
    }

    public function test_role_based_middleware()
    {
        $user = User::factory()->create(['role' => 'customer']);
        $this->actingAs($user);

        $response = $this->get('/admin/dashboard');
        $response->assertStatus(403); // Forbidden for non-admin

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->get('/admin/dashboard');
        $response->assertStatus(200); // Success for admin
    }

    public function test_user_profile_editing()
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'role' => 'customer',
        ]);
        $this->actingAs($user);

        $response = $this->put('/profile', [
            'name' => 'Updated Name',
            'contact_number' => '9876543210',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'contact_number' => '9876543210',
        ]);
    }
}