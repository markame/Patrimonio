<?php

namespace Database\Factories;

use App\Models\Convite;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Convite>
 */
class ConviteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'token_hash' => hash('sha256', Str::random(64)),
            'criado_por' => User::factory()->state(['administrador' => true]),
            'expira_em' => now()->addDays(7),
        ];
    }
}
