<?php

namespace App\Listeners;

use App\AbandonedCall;
use App\Callback;
use App\Cdr;
use App\Events\CallerAbandonEvent;
use App\Events\QueueAbandonEvent;
use App\QueueStat;
use Carbon\Carbon;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class CallerAbandonListener implements ShouldQueue
{
    public $delay = 60;
    public $tries = 2;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  CallerAbandonEvent  $event
     * @return void
     */
    public function handle(CallerAbandonEvent $event)
    {
        //sleep(5);
        $abandon = $event->abandon;
        $record = QueueStat::where([
            ['uniqueid' => $event->uniqueId],
            ['event', '11']
        ])->first();

        $number = $record->info2;
        Log::error("Calling back this number: " . $number);

        if($number !== null) {
            $callback = Callback::where("number", $number)->orderBy('created_at', 'desc')->first();
            $today = Carbon::now();
            if($callback == null) {
                //$this->info("Calling abandoned caller: " . $cdr->src);
                event(new QueueAbandonEvent($number, $abandon));
            } elseif($callback !== null && $today->diffInMinutes($callback->created_at) >= 5 && $number !== '2138658800') {
                /*$this->info(json_encode($callback));
                $this->info(json_encode($today));
                $this->info(json_encode($today->diffInMinutes($callback->created_at)));
                $this->info("Calling abandoned caller: " . $cdr->src);*/
                event(new QueueAbandonEvent($number, $abandon));
            }
        } else {
            Log::error("Number not found with UniqueId: " . $event->uniqueId);

        }
    }
}
