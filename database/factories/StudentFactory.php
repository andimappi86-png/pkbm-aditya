<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Program;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'user_id' => null,
            'nis' => fake()->unique()->numerify('#####'),
            'nisn' => fake()->unique()->numerify('##########'),
            'nik' => fake()->numerify('################'),
            'nama_lengkap' => fake()->name(),
            'nama_panggilan' => fake()->firstName(),
            'jenis_kelamin' => fake()->randomElement(['Laki-laki', 'Perempuan']),
            'tempat_lahir' => fake()->city(),
            'tanggal_lahir' => fake()->date('Y-m-d', '-10 years'),
            'agama' => fake()->randomElement(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu']),
            'status_perkawinan' => 'Belum Kawin',
            'alamat' => fake()->streetAddress(),
            'rt' => fake()->numerify('0#'),
            'rw' => fake()->numerify('0#'),
            'kelurahan' => fake()->citySuffix(),
            'kecamatan' => fake()->city(),
            'kabupaten' => fake()->city(),
            'provinsi' => 'Jawa Barat',
            'no_hp' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),

            'program_id' => Program::factory(),
            'tahun_ajaran_id' => AcademicYear::factory(),
            'rombel_id' => null,
            'status' => 'aktif',

            'nama_ayah' => fake()->name('male'),
            'nama_ibu' => fake()->name('female'),
            'nama_wali' => null,
            'no_hp_wali' => fake()->phoneNumber(),
            'alamat_wali' => fake()->streetAddress(),

            'foto' => null,
            'catatan' => null,
        ];
    }
}
