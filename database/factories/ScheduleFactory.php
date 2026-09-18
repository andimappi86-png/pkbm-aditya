<?php

namespace Database\Factories;

use App\Models\PenugasanMengajar;
use App\Models\Schedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Schedule>
 */
class ScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'penugasan_mengajar_id' => PenugasanMengajar::factory(),
            'hari' => $this->faker->randomElement(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu']),
            'jam_mulai' => '08:00',
            'jam_selesai' => '10:00',
            'ruangan' => 'Ruang '.$this->faker->randomLetter(),
            'status_aktif' => true,
        ];
    }
}
