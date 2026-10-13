<?php

namespace Tests\Fixtures;

use Illuminate\Database\Eloquent\Factories\Factory;

class ArticleModelFactory extends Factory
{
    protected $model = ArticleModel::class;

    public function definition(): array
    {
        return [
            'title'   => $this->faker->sentence(),
            'content' => $this->faker->paragraph(),
        ];
    }
}
