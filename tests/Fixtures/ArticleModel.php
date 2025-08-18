<?php
namespace Tests\Fixtures;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Article model for testing purposes.
 */
class ArticleModel extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $fillable = [
        'id',
        'title',
        'content',
    ];

    protected static function boot()
    {
        parent::boot();

        if (!\Schema::hasTable((new static)->getTable())) {
            \Schema::create((new static)->getTable(), function ($table) {
                $table->id();
                $table->string('title');
                $table->text('content');
                $table->timestamps();
            });
        }
    }

    public static function newFactory()
    {
        return ArticleModelFactory::new();
    }
}