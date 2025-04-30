<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => 'password', // Usar o Mutator/Cast 'hashed' do model
            'transaction_password' => '123456', // Usar o Mutator/Attribute 'hashed' do model
            'phone_number' => fake()->phoneNumber(),
            'date_of_birth' => fake()->dateTimeBetween('-70 years', '-18 years')->format('Y-m-d'),
            'address' => fake()->address(),
            'kyc_status' => fake()->randomElement(['pending', 'approved', 'rejected', 'resubmission_requested', null]),
            'status' => 'active',
            'preferred_currency' => fake()->randomElement(['USD', 'BRL', 'EUR', 'ARS', 'UYU']),
            'remember_token' => Str::random(10),
        ];
    }

    // Estados Úteis
    public function unverified(): static { return $this->state(fn (array $attributes) => ['email_verified_at' => null]); }
    public function pendingKyc(): static { return $this->state(fn (array $attributes) => ['kyc_status' => 'pending']); }
    public function approvedKyc(): static { return $this->state(fn (array $attributes) => ['kyc_status' => 'approved']); }
    public function blocked(): static { return $this->state(fn (array $attributes) => ['status' => 'blocked']); }
    public function withoutTransactionPassword(): static { return $this->state(fn (array $attributes) => ['transaction_password' => null]); }
    public function configure()
    {
         // Garante que a senha de transação seja hasheada se não for nula
         return $this->afterMaking(function (User $user) {
             if ($user->transaction_password) {
                 // O mutator já faz isso, mas podemos garantir aqui se necessário
                 // $user->transaction_password = $user->transaction_password; // Chama o mutator
             }
         });
    }
}