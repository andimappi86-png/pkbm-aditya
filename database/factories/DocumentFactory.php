<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'jenis_dokumen' => $this->faker->randomElement(['kk', 'akta_kelahiran', 'ijasah', 'foto']),
            'nama_file' => 'document_dummy.jpg',
            'file_path' => 'documents/document_dummy.jpg',
            'status_verifikasi' => 'pending',
            'uploaded_at' => now(),
        ];
    }
}
