<?php

namespace App\Listeners;

use App\Callback;
use App\Events\QueueAbandonEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PAMI\Client\Exception\ClientException;
use PAMI\Client\Impl\ClientImpl;
use PAMI\Message\Action\OriginateAction;

class QueueAbandonListeners
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
     * @param  QueueAbandonEvent  $event
     * @return void
     */
    public function handle(QueueAbandonEvent $event)
    {
        $client = new ClientImpl($this->getOptions());
        if(Str::startsWith($event->number, '0')) {
            $action = new OriginateAction("SIP/" . $event->number . "@TCLPrimary");
            Log::info("Dialing without zero: " . $event->number);
        } else {
            $action = new OriginateAction("SIP/0" . $event->number . "@TCLPrimary");
            Log::info("Dialing with zero: " . $event->number);
        }
        //$action->setApplication('queue');
        $action->setContext('from-trunk-sip-TCLPrimary');
        $action->setPriority('1');
        $action->setExtension('2138658800');
        $action->setVariable('CALLERID(num)', $event->number);
        //$action->setVariable('CALLERID(num)', '2138658800');
        $action->setCallerId('2138658800');
        $action->setVariable('CDR(userfield)', 'callback');
        $action->setVariable('CDR(src)', $event->number);
        //$action->setData('10');
        $action->setAsync(true);

        try {
            $client->open();
            $resp = $client->send($action);
            $client->close();
            if($resp->getKey('response') == "Success") {
                Callback::create([
                    'number' =>  $event->number
                ]);
                Log::info("Callback has been initiated on number: " . $event->number);
            }
        } catch (ClientException $e) {
            Log::error($e->getMessage());
            //Log::error($e->getTraceAsString());
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
