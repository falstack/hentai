<?php

namespace App\Console\Jobs;

use App\Http\Repositories\PinRepository;
use App\Http\Repositories\Repository;
use App\Http\Repositories\UserRepository;
use App\Models\Bangumi;
use App\Models\BangumiQuestion;
use App\Models\Idol;
use App\Models\Pin;
use App\Models\Tag;
use App\Services\OpenSearch\Search;
use App\Services\Qiniu\Qshell;
use App\Services\Spider\BangumiSource;
use App\Services\Spider\Query;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Test';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'test job';
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $list = Pin
            ::where('content_type', 2)
            ->where('main_area_slug', '<>', '')
            ->pluck('slug', 'main_area_slug');

        $pinRepository = new PinRepository();
        foreach ($list as $tagSlug => $pinSlug)
        {
            $bangumiSlug = Tag
                ::where('slug', $tagSlug)
                ->pluck('migration_slug')
                ->first();

            if (!$bangumiSlug)
            {
                continue;
            }

            $pin = $pinRepository->item($pinSlug);
            if (!$pin)
            {
                Pin::where('slug', $pinSlug)->delete();
                continue;
            }

            $content = $pin->content;
            $vote = '';
            $title = '';
            foreach ($content as $row)
            {
                if ($row->type === 'vote')
                {
                    $vote = $row->data;
                }
                else if ($row->type === 'title')
                {
                    $title = $row->data['text'];
                }
            }

            if (!$vote)
            {
                Pin::where('slug', $pinSlug)->delete();
                continue;
            }

            $answers = [];
            foreach ($vote->items as $item)
            {
                $answers[$item->id] = $item->text;
            }

            BangumiQuestion::create([
                'title' => $title,
                'bangumi_slug' => $bangumiSlug,
                'user_slug' => $pin->author->slug,
                'answers' => json_encode($answers),
                'right_id' => $vote->right_ids[0],
                'status' => 0
            ]);

            Pin::where('slug', $pinSlug)->delete();
        }

        return true;
    }
}
