<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Idol extends Model
{
    protected $table = 'idols';

    protected $fillable = [
        'slug',
        'title',
        'alias',
        'intro',
        'rank',
        'avatar',
        'source_id',
        'bangumi_slug',
        'lover_slug',
        'is_newbie',
        'market_price', // stock_price * stock_count
        'stock_price',
        'stock_count',
        'fans_count',
        'coin_count',
        'migration_state'
    ];

    protected $casts = [
        'is_newbie' => 'boolean'
    ];

    public function setAvatarAttribute($url)
    {
        $this->attributes['avatar'] = trimImage($url);
    }

    public function getAvatarAttribute($avatar)
    {
        return patchImage($avatar, 'default-avatar');
    }

    public function bangumi()
    {
        return $this->belongsTo('App\Models\Bangumi', 'bangumi_slug', 'slug');
    }

    public function lover()
    {
        return $this->hasOne('App\User', 'slug', 'lover_slug');
    }
}
