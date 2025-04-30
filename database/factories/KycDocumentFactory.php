<?php

namespace Database\Factories;

use App\Models\KycDocument;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage; // Para simular upload

class KycDocumentFactory extends Factory
{
    protected $model = KycDocument::class;

    public function definition(): array
    {
        $type = fake()->randomElement(['selfie', 'doc_front', 'doc_back']);
        // Simular upload para disco fake/local para testes
        // Storage::fake('kyc_uploads'); // Chamar isso no seu teste
        // $filePath = 'kyc/user_'.fake()->randomNumber(5).'/'.$type.'_'.time().'.jpg';
        // Storage::disk('kyc_uploads')->put($filePath, 'fake_content');

        return [
            'user_id' => User::factory(),
            'type' => $type,
            // Usar caminho simulado - Arquivo real não é criado pela factory
            'file_path' => 'kyc/user_' . fake()->randomNumber(5) . '/' . $type . '_' . time() . '.jpg',
            'status' => 'uploaded',
            'uploaded_at' => now(),
        ];
    }

    public function type(string $docType): static
    {
        return $this->state(fn (array $attributes) => ['type' => $docType]);
    }
}