<?php

namespace Database\Factories;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Hashed once per run — bcrypt is slow and every test seeds users.
     */
    protected static ?string $password = null;

    /**
     * A customer with no branch.
     *
     * Note this table has no email_verified_at or remember_token column; auth
     * here is hand-rolled on the Auth facade rather than laravel/ui scaffolding.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => 'customer',
            'branch_id' => null,
            'nationality' => fake()->country(),
            'contact_number' => fake()->numerify('07########'),
        ];
    }

    /** Front-desk clerk, pinned to a branch. */
    public function clerk(?Branch $branch = null): static
    {
        return $this->state(fn () => [
            'role' => 'clerk',
            'branch_id' => $branch?->id ?? Branch::factory(),
        ]);
    }

    /** Branch manager, pinned to a branch. */
    public function manager(?Branch $branch = null): static
    {
        return $this->state(fn () => [
            'role' => 'manager',
            'branch_id' => $branch?->id ?? Branch::factory(),
        ]);
    }

    /** Admin — bypasses every role check by design (see RoleMiddleware). */
    public function admin(): static
    {
        return $this->state(fn () => [
            'role' => 'admin',
            'branch_id' => null,
        ]);
    }
}
