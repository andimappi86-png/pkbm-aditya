<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnnouncementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'judul' => $this->faker->sentence(),
            'isi' => $this->faker->paragraphs(3, true),
            'kategori' => $this->faker->randomElement(['Akademik', 'Umum', 'Keuangan']),
            'status_aktif' => true,
            'published_at' => now(),
        ];
    }
}
