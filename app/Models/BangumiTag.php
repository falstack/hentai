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
}
