<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Haberler>
 */
class HaberlerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // factory ile fake data oluşturma
        $baslik = $this->faker->sentence(rand(4, 8));

        return [
            'baslik' => $baslik,
            'icerik' => $this->faker->paragraphs(rand(5, 15), true),
            'resim' => $this->faker->boolean(30) ? $this->faker->imageUrl(640, 480, 'news') : null,
            'created_at' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'updated_at' => now(),
        ];
    }
}
