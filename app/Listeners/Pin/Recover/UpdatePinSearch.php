<?php


namespace App\Listeners\Pin\Recover;


use App\Http\Repositories\PinRepository;
use App\Models\Search;

class UpdatePinSearch
{
    public function __construct()
    {

    }

    public function handle(\App\Events\Pin\Recover $event)
    {
        if (!$event->published)
        {
            return;
        }

        $pin = $event->pin;
        $search = Search
            ::where('type', 2)
            ->where('slug', $pin->slug)
            ->first();

        $pinRepository = new PinRepository();
        $txtPin = $pinRepository->item($pin->slug);

        $text = $txtPin->title->text . '|' . $txtPin->intro;

        if (null === $search)
        {
            Search::create([
                'type' => 2,
                'slug' => $pin->slug,
                'text' => $text
            ]);
        }
        else
        {
            $search->update([
                'text' => $text
            ]);
        }
    }
}
