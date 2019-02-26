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
            $action = new OriginateAction("SIP/" . $this->argument('number') . "@TCLPrimary");
            $this->info("Dialing without zero: " . $this->argument('number'));
        } else {
            $action = new OriginateAction("SIP/0" . $this->argument('number') . "@TCLPrimary");
            $this->info("Dialing with zero: " . $this->argument('number'));
        }
        //$action->setApplication('queue');
        $action->setContext('from-trunk-sip-TCLPrimary');
        $action->setPriority('1');
        $action->setExtension('2138658800');
        //$action->setVariable('CALLERID(num)', $event->number);
        $action->setVariable('CALLERID(num)', $this->argument('number'));
        //$action->setVariable('CALLERID(all)', '2138658800');
        $action->setCallerId('2138658800');
        $action->setVariable('CDR(userfield)', 'callback');
        //$action->setVariable('CDR(src)', $cdr->src);
        //$action->setData('10');
        $action->setAsync(true);

        try {
            $client->open();
            $resp = $client->send($action);
            $client->close();
            if($resp->getKey('response') == "Success") {
                $this->info("Callback has been initiated on number: " . $this->argument('number'));
            } else {
                $this->info(json_encode($resp));
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
            'host' => '172.54.5.18',
            'scheme' => 'tcp://',
            'port' => 5038,
            'username' => 'callback_mgr',
            'secret' => '0chanc3yoadjasldjkasl',
            'connect_timeout' => 1000,
            'read_timeout' => 1000
        ];
    }
}
