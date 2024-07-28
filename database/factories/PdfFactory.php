<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Pdf;
use App\Models\Section;

class PdfFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Pdf::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'file' => $this->faker->regexify('[A-Za-z0-9]{4048}'),
            'name' => $this->faker->name(),
            'section_id' => Section::factory(),
            'chatgpt_file_id' => $this->faker->regexify('[A-Za-z0-9]{255}'),
        ];
    }
}
