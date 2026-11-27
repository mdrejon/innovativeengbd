<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Blog article. Knowledge-base articles live in the same table with a
 * different `type` (see KnowledgeArticle), so every query here is scoped.
 */
class BlogPost extends Model
{
    use HasFactory;

    /**
     * Value of the `type` column for this kind of post.
     */
    public const TYPE = 'blog';

    protected $table = 'blog_posts';

    protected $fillable = [
        'category',
        'title',
        'slug',
        'excerpt',
        'content',
        'icon',
        'featured_image',
        'author_name',
        'read_time',
        'sort_order',
        'is_published',
        'published_at',
        'meta_title',
        'meta_description',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'sort_order' => 'integer',
            'views' => 'integer',
            'helpful_yes' => 'integer',
            'helpful_no' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        $type = static::TYPE;

        static::addGlobalScope('type', fn (Builder $query) => $query->where('type', $type));
        static::creating(fn (self $post) => $post->type = $type);

        static::saved(fn () => Page::flushContentCache());
        static::deleted(fn () => Page::flushContentCache());
    }

    /**
     * @param  Builder<static>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true)
            ->where(fn (Builder $q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->latest('published_at');
    }
}
