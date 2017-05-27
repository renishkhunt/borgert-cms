<?php

namespace App\Models\Admin\Blog;

use Kyslik\ColumnSortable\Sortable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Posts extends Model
{
    use SoftDeletes, Sortable;

    protected $table = 'blog_posts';

    protected $dates = ['publish_at'];

    protected $fillable = [
        'category_id',
        'title',
        'description',
    ];

    protected $sortable = [
        'id',
        'category_id',
        'title',
        'status',
        'created_at',
        'publish_at',
    ];

    // -------------------------------------------------------------------------------

    public function categorys_id()
    {
        return $this->hasMany('App\Models\Admin\Blog\CategoryPosts','post_id');
    }

    // -------------------------------------------------------------------------------

    public function tags_id()
    {
        return $this->hasMany('App\Models\Admin\Blog\TagsPosts','post_id');
    }

    // -------------------------------------------------------------------------------

    public function comments()
    {
        return $this->hasMany('App\Models\Admin\Blog\Comments', 'post_id');
    }

    // -------------------------------------------------------------------------------

    public function image($thumb = false)
    {
        $img = collect(\Storage::disk('uploads')->files('blog/posts/'.$this->id.'/'.($thumb ? 'thumbnail/' : '')))->first();

        return $img ? public_path('uploads/'.$img) : null;
    }

    // -------------------------------------------------------------------------------

    public function seo($type = null)
    {
        if ($type === null) {
            return;
        }

        if ($type === 'title') {
            if (! empty($this->seo_title)) {
                return str_limit($this->seo_title, 70);
            }

            return str_limit($this->title, 70);
        }

        if ($type === 'description') {
            if (! empty($this->seo_description)) {
                return str_limit($this->seo_description, 170);
            }

            return str_limit($this->description, 170);
        }

        if ($type === 'keywords') {
            return $this->seo_keywords;
        }
    }

    // -------------------------------------------------------------------------------
}
