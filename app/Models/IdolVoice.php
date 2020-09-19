<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IdolVoice extends Model
{
    use SoftDeletes;

    protected $table = 'idol_voices';

    protected $fillable = [
        'from_slug',
        'from_type',    // 0：偶像角色，1：用户
        'src',
        'meta',
        'text'
    ];

    public function setSrcAttribute($url)
    {
        $this->attributes['src'] = trimImage($url);
    }

    public function getSrcAttribute($avatar)
    {
        return patchImage($avatar, 'default-avatar');
    }
}
