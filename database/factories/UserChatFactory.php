<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\MasterUserChat;
use App\Models\UserChat;

class UserChatFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserChat::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'master_user_chat_id' => MasterUserChat::factory(),
            'question' => $this->faker->regexify('[A-Za-z0-9]{4096}'),
            'answer' => $this->faker->text(),
        ];
    }
}
