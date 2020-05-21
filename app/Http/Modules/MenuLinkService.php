<?php


namespace App\Http\Modules;


use App\Http\Repositories\Repository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class MenuLinkService
{
    protected $type_table = 'menu_types';
    protected $link_table = 'menu_links';

    public function menus()
    {
        $repository = new Repository();

        $str = $repository->RedisItem($this->menuCacheKey(), function ()
        {
            $menus = DB
                ::table($this->type_table)
                ->orderBy('order', 'ASC')
                ->select('id', 'name')
                ->get()
                ->toArray();

            $links = DB
                ::table($this->link_table)
                ->whereNull('deleted_at')
                ->select('id', 'name', 'href', 'type')
                ->get()
                ->toArray();

            foreach ($menus as $i => $menu)
            {
                $children = [];
                $type = $menu->id;

                foreach ($links as $link)
                {
                    if ($link->type == $type)
                    {
                        $children[] = $link;
                    }
                }

                $menus[$i]->count = 0;
                $menus[$i]->children = $children;
            }

            foreach ($menus as $i => $menu)
            {
                if (!count($menu->children))
                {
                    unset($menus[$i]);
                }
            }

            return json_encode($menus);
        });

        return gettype($str) === 'string' ? json_decode($str, true) : $str;
    }

    public function count()
    {
        $repository = new Repository();

        return $repository->RedisSort($this->counterCacheKey(), function ()
        {
            $ids = DB
                ::table($this->type_table)
                ->pluck('id')
                ->toArray();

            $result = [];
            foreach ($ids as $id)
            {
                $result[$id] = 0;
            }

            return $result;

        }, ['with_score' => true]);
    }

    public function createType($name)
    {
        $name = trim($name);

        $has = DB
            ::table($this->type_table)
            ->where('name', $name)
            ->count();

        if ($has)
        {
            return false;
        }

        $total = DB::table($this->type_table)->count();
        $now = Carbon::now();

        DB
            ::table($this->type_table)
            ->insert([
                'name' => $name,
                'order' => $total,
                'created_at' => $now,
                'updated_at' => $now
            ]);

        return true;
    }

    public function createLink($name, $href, $type)
    {
        $name = trim($name);
        $href = trim(explode('?', $href)[0]);

        $has = DB
            ::table($this->link_table)
            ->where('href', $href)
            ->count();

        if ($has)
        {
            return false;
        }

        $now = Carbon::now();

        DB
            ::table($this->link_table)
            ->insert([
                'name' => $name,
                'href' => $href,
                'type' => $type,
                'created_at' => $now,
                'updated_at' => $now
            ]);

        Redis::DEL($this->menuCacheKey());

        return true;
    }

    public function deleteLink($id)
    {
        DB
            ::table($this->link_table)
            ->where('id', $id)
            ->update([
                'deleted_at' => Carbon::now()
            ]);

        Redis::DEL($this->menuCacheKey());

        return;
    }

    public function allLinks()
    {
        return DB
            ::table($this->link_table)
            ->get();
    }

    public function allTypes()
    {
        return DB
            ::table($this->type_table)
            ->get();
    }

    public function reportLink($id, $type)
    {
        DB
            ::table($this->link_table)
            ->where('id', $id)
            ->increment('click_count');

        $repository = new Repository();
        $repository->SortAdd($this->counterCacheKey(), $type, 1);
    }

    protected function menuCacheKey()
    {
        return 'menu_links_list';
    }

    protected function counterCacheKey()
    {
        return 'menu_links_visit' . ':' . strtotime(date('Y-m-d', time()));
    }
}
