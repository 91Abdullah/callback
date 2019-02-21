<?php

namespace App\Listeners;

use App\Callback;
use App\Events\QueueAbandonEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
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
        $action = new OriginateAction("SIP/0" . $event->number . "@TCL");
        //$action->setApplication('queue');
        $action->setContext('from-trunk-sip-TCL');
        $action->setPriority('1');
        $action->setExtension('2138797457');
        $action->setVariable('CALLERID(num)', $event->number);
        $action->setVariable('CDR(userfield)', 'callback');
        //$action->setVariable('CDR(src)', $cdr->src);
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
            }
        } catch (ClientException $e) {
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
        }
    }

    private function getOptions()
    {
        return $options = [
            'host' => '10.0.0.80',
            'scheme' => 'tcp://',
            'port' => 5038,
            'username' => 'remote_mgr',
            'secret' => '0chanc3yo',
            'connect_timeout' => 10,
            'read_timeout' => 10
        ];
    }
}
