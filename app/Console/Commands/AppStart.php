<?php

namespace App\Console\Commands;

use App\AbandonedCall;
use App\Callback;
use App\Cdr;
use App\Events\QueueAbandonEvent;
use Carbon\Carbon;
use Illuminate\Console\Command;
use PAMI\Client\Exception\ClientException;
use PAMI\Client\Impl\ClientImpl;
use PAMI\Message\Action\OriginateAction;
use PAMI\Message\Event\QueueCallerAbandonEvent;

class AppStart extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to start application';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $client = new ClientImpl($this->returnOptions());
        $client->registerEventListener(function ($event) use ($client) {
            //$this->info(dd($event));
            $abandon = AbandonedCall::create([
                'number' => '',
                'status' => false,
                'abandontime' => Carbon::now()->format('Y-m-d H:i:s'),
                'queue' => $event->getKey('queue'),
                'position' => $event->getKey('position'),
                'originalposition' => $event->getKey('originalposition'),
                'holdtime' => $event->getKey('holdtime'),
                'uniqueid' => $event->getKey('uniqueid')
            ]);
            sleep(2);
            $cdr = Cdr::findOrFail($event->getKey('uniqueid'));
            $abandon->number = $cdr->src;
            $abandon->save();
            sleep(2);
            $callback = Callback::where("number", $cdr->src)->orderBy('created_at', 'desc')->first();
            $today = Carbon::now();
            if($callback == null) {
                event(new QueueAbandonEvent($cdr->src, $abandon));
            } elseif($callback !== null && $today->diffInMinutes($callback->created_at) >= 5) {
                $this->info(json_encode($callback));
                $this->info(json_encode($today));
                $this->info(json_encode($today->diffInMinutes($callback->created_at)));
                event(new QueueAbandonEvent($cdr->src, $abandon));
            }
        }, function ($event) {
            return $event instanceof QueueCallerAbandonEvent;
        });

        try {
            $client->open();
            while (true) {
                usleep(1000);
                $client->process();
            }
        } catch (ClientException $e) {
            $this->info($e->getMessage());
            $this->info($e->getTraceAsString());
        }
    }

    private function returnOptions()
    {
        return $options = [
            'host' => '10.0.0.80',
            'scheme' => 'tcp://',
            'port' => 5038,
            'username' => 'remote_mgr',
            'secret' => '0chanc3yo',
            'connect_timeout' => 100,
            'read_timeout' => 100
        ];
    }
}
