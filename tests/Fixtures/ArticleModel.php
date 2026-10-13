<?php

namespace Tests\Fixtures;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticleModel extends Model
{
    use HasFactory;

    protected $table = 'articles';

    protected $guarded = [];

    public static function newFactory(): ArticleModelFactory
    {
        return ArticleModelFactory::new();
    }
}
