<?php

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\AdminUser;
use App\Models\User;
use App\Models\Asset;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    public function definition(): array
    {
        // Define aleatoriamente o causador e o sujeito
        $causer = AdminUser::inRandomOrder()->first() ?? AdminUser::factory()->create();
        $subjectModel = fake()->randomElement([User::class, Asset::class]);
        $subject = $subjectModel::inRandomOrder()->first() ?? $subjectModel::factory()->create();

        $action = 'generic_action';
        $properties = ['details' => fake()->sentence()];

        if ($subject instanceof User) {
            $action = fake()->randomElement(['user_blocked', 'user_unblocked', 'kyc_approved', 'balance_adjusted']);
            if ($action === 'balance_adjusted') {
                 $properties = ['old' => ['balance' => fake()->randomFloat(2)], 'new' => ['balance' => fake()->randomFloat(2)], 'currency' => 'USD', 'reason' => fake()->sentence()];
            } else {
                 $properties = ['old' => ['status' => 'active'], 'new' => ['status' => 'blocked']];
            }
        } elseif ($subject instanceof Asset) {
             $action = fake()->randomElement(['asset_created', 'asset_updated', 'asset_deleted']);
             $properties = ['old' => ['price' => fake()->randomFloat(2)], 'new' => ['price' => fake()->randomFloat(2)]];
        }


        return [
            'causer_id' => $causer->id,
            'causer_type' => get_class($causer),
            'action' => $action,
            'subject_id' => $subject->id,
            'subject_type' => get_class($subject),
            'properties' => $properties, // O mutator do Setting model cuida do encode/decode
            'ip_address' => fake()->ipv4(),
        ];
    }

     // Estados para ações específicas
     public function causedBy(Model $causer): static { return $this->state(fn (array $a) => ['causer_id' => $causer->id, 'causer_type' => get_class($causer)]); }
     public function onSubject(Model $subject): static { return $this->state(fn (array $a) => ['subject_id' => $subject->id, 'subject_type' => get_class($subject)]); }
     public function action(string $actionName): static { return $this->state(fn (array $a) => ['action' => $actionName]); }
}