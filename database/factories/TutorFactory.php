<?php

namespace Database\Factories;

use App\Models\Tutor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tutor>
 */
class TutorFactory extends Factory
{
    protected $model = Tutor::class;

    public function definition(): array
    {
        return [
            'user_id' => null,
            'nomor_induk' => fake()->unique()->numerify('TUTOR-####'),
            'nik' => fake()->unique()->numerify('7600000000######'),
            'nama_lengkap' => fake()->name(),
            'nama_panggilan' => fake()->firstName(),
            'jenis_kelamin' => fake()->randomElement(['Laki-laki', 'Perempuan']),
            'tempat_lahir' => fake()->city(),
            'tanggal_lahir' => fake()->date('Y-m-d', '-30 years'),
            'agama' => fake()->randomElement(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha']),
            'alamat' => fake()->streetAddress(),
            'no_hp' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'pendidikan_terakhir' => fake()->randomElement(['S1 Pendidikan', 'S1 Non Kependidikan', 'S2', 'D3']),
            'bidang_keahlian' => fake()->randomElement(['Matematika', 'Bahasa Indonesia', 'IPA', 'IPS', 'Komputer']),
            'status_tutor' => 'aktif',
            'tanggal_mulai_mengajar' => fake()->date('Y-m-d', '-2 years'),
            'foto' => null,
            'catatan' => null,
        ];
    }
}
