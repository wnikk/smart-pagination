<?php
namespace Tests\Fixtures;

use Tests\Fixtures\ArticleModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class ArticleModelFactory extends Factory
{
    protected $model = ArticleModel::class;

    public function definition()
    {
        return [
            // define your fields here
            'title' => $this->faker->sentence,
            'content' => $this->faker->paragraph,
        ];
    }
}