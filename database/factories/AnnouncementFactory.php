<?php

namespace Database\Factories;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Announcement>
 */
class AnnouncementFactory extends Factory
{
    protected $model = Announcement::class;

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
