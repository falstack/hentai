<?php

namespace App\Console\Jobs;

use App\Http\Modules\Counter\IdolPatchCounter;
use App\Http\Modules\VirtualCoinService;
use App\Http\Repositories\IdolRepository;
use App\Models\Idol;
use App\Models\IdolFans;
use App\Services\WilsonScoreInterval;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class UpdateIdolMarketPrice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'UpdateIdolMarketPrice';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'update idol market price';
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $total = Idol
            ::sum('coin_count');

        $list = Idol
            ::where('fans_count', '>', 0)
            ->orderBy('market_price', 'DESC')
            ->orderBy('stock_price', 'DESC')
            ->get();

        $virtualCoinService = new VirtualCoinService();
        $idolRepository = new IdolRepository();
        $idolPatchCounter = new IdolPatchCounter();

        foreach ($list as $index => $item)
        {
            $score = $item->coin_count;
            $calc = new WilsonScoreInterval($score, $total - $score);
            $rate = $calc->score();
            $price = $virtualCoinService->calculate($rate * $total / $score + 1);

            DB
                ::table('idols')
                ->where('slug', $item->slug)
                ->update([
                    'lover_slug' => IdolFans::where('idol_slug',  $item->slug)->orderBy('stock_count', 'DESC')->pluck('user_slug')->first(),
                    'stock_price' => $price,
                    'market_price' => $price * $item->stock_count,
                    'rank' => $index + 1
                ]);

            $idolRepository->item($item->slug, true);
            $idolPatchCounter->all($item->slug, true);
        }

        return true;
    }
}
