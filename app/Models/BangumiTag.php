<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class BangumiTag extends Model
{
    protected $table = 'bangumi_tags';

    protected $fillable = [
        'slug',
        'name'
    ];

    public function tags()
    {
        return $this->belongsToMany(
            'App\Models\Bangumi',
            'bangumi_tag_relations',
            'tag_slug',
            'bangumi_slug',
            'slug',
            'slug'
        );
    }
}
