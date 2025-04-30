<?php

namespace Database\Factories;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Factories\Factory;

class SettingFactory extends Factory
{
    protected $model = Setting::class;

    public function definition(): array
    {
        $key = fake()->unique()->slug(3, false); // Ex: word1_word2_word3
        $isSerialized = fake()->boolean(15); // 15% chance
        $value = $isSerialized
                 ? json_encode(['option' => fake()->word(), 'enabled' => fake()->boolean(), 'limit' => fake()->randomNumber(3)])
                 : fake()->sentence();

        return [
            'key' => $key,
            'value' => $value,
            'serialized' => $isSerialized,
        ];
    }

    public function named(string $keyName, $keyValue, bool $serial = false): static
    {
        return $this->state(fn (array $attributes) => [
            'key' => $keyName,
            'value' => $serial ? json_encode($keyValue) : $keyValue,
            'serialized' => $serial,
        ]);
    }
}