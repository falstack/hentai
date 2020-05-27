<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class BangumiSerialization extends Model
{
    protected $table = 'bangumi_serialization';

    protected $fillable = [
        'bangumi_id',
        'season_id',
        'title',
        'status',
        'current',
        'raw_id',
        'site',
        'url',
        'broadcast_time',
        'created_at',
        'update_at',
    ];
}
