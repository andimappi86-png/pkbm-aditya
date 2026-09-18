<?php

namespace Database\Seeders;

use App\Models\Tutor;
use Database\Factories\TutorFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TutorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Tutor::truncate();

        $factory = new TutorFactory;
        $tutors = $factory->count(20)->create();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info("✅ {$tutors->count()} tutor seeded successfully");
    }
}
