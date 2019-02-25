<?php

namespace App\Listeners;

use App\AbandonedCall;
use App\Callback;
use App\Cdr;
use App\Events\CallerAbandonEvent;
use App\Events\QueueAbandonEvent;
use Carbon\Carbon;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CallerAbandonListener
{
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
        sleep(5);
        $abandon = $event->abandon;
        $cdr = Cdr::find($event->uniqueId);
        if($cdr == null) {
            // wait for 5 secs then find again
            sleep(5);
            $cdr = Cdr::find($event->uniqueId);
        }

        $callback = Callback::where("number", $cdr->src)->orderBy('created_at', 'desc')->first();
        $today = Carbon::now();
        if($callback == null) {
            //$this->info("Calling abandoned caller: " . $cdr->src);
            event(new QueueAbandonEvent($cdr->src, $abandon));
        } elseif($callback !== null && $today->diffInMinutes($callback->created_at) >= 5) {
            /*$this->info(json_encode($callback));
            $this->info(json_encode($today));
            $this->info(json_encode($today->diffInMinutes($callback->created_at)));
            $this->info("Calling abandoned caller: " . $cdr->src);*/
            event(new QueueAbandonEvent($cdr->src, $abandon));
        }
    }
}