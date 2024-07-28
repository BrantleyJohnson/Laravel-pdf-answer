<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\MasterUserChat;
use App\Models\User;

class MasterUserChatFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MasterUserChat::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'chatgpt_id' => $this->faker->regexify('[A-Za-z0-9]{1024}'),
            'name' => $this->faker->name(),
            'sharable_link' => $this->faker->regexify('[A-Za-z0-9]{2024}'),
            'share_name' => $this->faker->regexify('[A-Za-z0-9]{5}'),
            'is_archive' => $this->faker->regexify('[A-Za-z0-9]{5}'),
            'user_id' => User::factory(),
        ];
    }
}
