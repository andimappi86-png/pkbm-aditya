<?php

namespace Database\Factories;

use App\Models\Forum;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ForumCommentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'forum_id' => Forum::factory(),
            'user_id' => User::factory(),
            'isi' => $this->faker->sentence(10),
        ];
    }
}
