<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Material;
use Illuminate\Database\Eloquent\Factories\Factory;

// Factory pembuat data Material palsu (otomatis membuat course terkait).
class MaterialFactory extends Factory
{
    protected $model = Material::class;

    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'title'     => $this->faker->sentence(3),
            'content'   => $this->faker->paragraphs(3, true),
            'order'     => $this->faker->numberBetween(1, 10),
        ];
    }
}
