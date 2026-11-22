<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Content extends Model
{
    use SoftDeletes;

    protected $table = 'contents';

    protected $casts = [
        'published_at' => 'datetime',
        'tags'         => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (Content $content) {
            if (blank($content->slug) && filled($content->title)) {
                $content->slug = static::generateUniqueSlug($content->title);
            }
        });
    }

    public static function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($title) ?: 'konten-edukasi';
        $slug = $baseSlug;
        $counter = 2;

        while (
            static::withTrashed()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}