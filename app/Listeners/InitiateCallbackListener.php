<?php

namespace App\Listeners;

use App\Callback;
use App\Events\InitiateCallbackEvent;
use App\QueueStat;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PAMI\Client\Exception\ClientException;
use PAMI\Client\Impl\ClientImpl;
use PAMI\Message\Action\OriginateAction;

class InitiateCallbackListener implements ShouldQueue
{
    public $delay = 30;
    public $tries = 1;

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
     * @param  InitiateCallbackEvent  $event
     * @return void
     */
    public function handle(InitiateCallbackEvent $event)
    {
        // variables
        $callerID = "2138797022";
        $masterNo = "2138797457";
        $extension = "2138797457";
        $trunk = "TCL";
        $context = "app-setcid";
        $priority = "1";

        $uniqueId = $event->uniqueid;

        $callerId = QueueStat::where([
            ['uniqueid', $uniqueId],
            ['qevent', '11']
        ])->first();

        Log::info(json_encode($callerId));
        Log::info(json_encode($uniqueId));

        if($callerId !== null && $callerId->info2 !== $masterNo) {
            $client = new ClientImpl($this->getOptions());
            if(Str::startsWith($callerId->info2, '0')) {
                $action = new OriginateAction("SIP/" . $callerId->info2 . "@" . $trunk);
                Log::info("Dialing without zero: " . $callerId->info2);
            } else {
                $action = new OriginateAction("SIP/0" . $callerId->info2 . "@TCL");
                Log::info("Dialing with zero: " . $callerId->info2);
            }

            $action->setContext($context);
            $action->setPriority($priority);
            $action->setExtension($callerId->info2);
            $action->setCallerId($callerID);
            $action->setVariable('CDR(userfield)', 'callback');
            $action->setAsync(true);

            try {
                $client->open();
                $resp = $client->send($action);
                $client->close();
                if($resp->getKey('response') == "Success") {
                    Callback::create([
                        'number' =>  $callerId->info2
                    ]);
                    Log::info("Callback has been initiated on number: " . $callerId->info2);
                }
            } catch (ClientException $e) {
                Log::error($e->getMessage());
                //Log::error($e->getTraceAsString());
            }
        }
    }

    private function getOptions()
    {
        return $options = [
            //'host' => '172.54.5.18',
            'host' => '10.0.0.80',
            'scheme' => 'tcp://',
            'port' => 5038,
            'username' => 'callback_mgr',
            //'secret' => '0chanc3yoadjasldjkasl',
            'secret' => '0chanc3yo',
            'connect_timeout' => 1000,
            'read_timeout' => 1000
        ];
    }
}
