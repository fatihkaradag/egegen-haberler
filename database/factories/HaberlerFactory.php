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

        $baslik = $this->faker->sentence(rand(4, 8));

        return [
            'baslik' => $baslik,
            'icerik' => $this->faker->paragraphs(rand(5, 15), true),
            'slug' => Str::slug($baslik) . '-' . $this->faker->unique()->randomNumber(5),
            'resim' => $this->faker->boolean(30) ? $this->faker->imageUrl(640, 480, 'news') : null,
            'yayindami' => $this->faker->boolean(85), // %85 aktif haber
            'created_at' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'updated_at' => now(),
        ];
    }
}
