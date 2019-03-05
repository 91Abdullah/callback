<?php

namespace App\Console\Commands;

use App\Callback;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PAMI\Client\Exception\ClientException;
use PAMI\Client\Impl\ClientImpl;
use PAMI\Message\Action\OriginateAction;

class Originate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'originate:start {number}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $client = new ClientImpl($this->getOptions());
        if(Str::startsWith($this->argument('number'), '0')) {
            $action = new OriginateAction("SIP/" . $this->argument('number') . "@TCL");
            $this->info("Dialing without zero: " . $this->argument('number'));
        } else {
            $action = new OriginateAction("SIP/0" . $this->argument('number') . "@TCL");
            $this->info("Dialing with zero: " . $this->argument('number'));
        }
        //$action->setApplication('queue');
        $action->setContext('app-setcid');
        $action->setPriority('1');
        $action->setExtension($this->argument('number'));
        //$action->setVariable('CALLERID(num)', $event->number);
        $action->setVariable('CALLERID(num)', $this->argument('number'));
        //$action->setVariable('CALLERID(all)', '2138658800');
        $action->setCallerId('2138797022');
        $action->setVariable('CDR(userfield)', 'callback');
        $action->setVariable('CDR(src)', $this->argument('number'));
        //$action->setData('10');
        $action->setAsync(true);

        try {
            $client->open();
            $resp = $client->send($action);
            $client->close();
            if($resp->getKey('response') == "Success") {
                $this->info("Callback has been initiated on number: " . $this->argument('number'));
                $this->info($resp->getKey('message'));
            } elseif($resp->getKey('response') == "Error") {
                $this->info($resp->getKey('message'));
            } else {
                $this->info(var_dump($resp));
            }
        } catch (ClientException $e) {
            $this->info($e->getMessage());
            //Log::error($e->getTraceAsString());
        }
        return null;
    }

    public function getOptions()
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
