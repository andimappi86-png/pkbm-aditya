<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Ppdb;
use App\Models\Program;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ppdb>
 */
class PpdbFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'nomor_pendaftaran' => 'PPDB-'.date('Y').'-'.$this->faker->unique()->numerify('####'),
            'program_id' => Program::factory(),
            'tahun_ajaran_id' => AcademicYear::factory(),
            'nik' => $this->faker->numerify('################'),
            'nisn' => $this->faker->numerify('##########'),
            'nama_lengkap' => $this->faker->name(),
            'nama_panggilan' => $this->faker->firstName(),
            'jenis_kelamin' => $this->faker->randomElement(['L', 'P']),
            'tempat_lahir' => $this->faker->city(),
            'tanggal_lahir' => $this->faker->date(),
            'agama' => $this->faker->randomElement(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha']),
            'alamat' => $this->faker->address(),
            'kabupaten' => 'Mamuju',
            'provinsi' => 'Sulawesi Barat',
            'no_hp' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'nama_ayah' => $this->faker->name('male'),
            'nama_ibu' => $this->faker->name('female'),
            'status' => 'pending',
        ];
    }
}
