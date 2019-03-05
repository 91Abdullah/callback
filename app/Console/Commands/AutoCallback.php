<?php

namespace App\Console\Commands;

use App\Events\InitiateCallbackEvent;
use App\QueueStat;
use Clue\React\Ami\ActionSender;
use Clue\React\Ami\Client;
use Clue\React\Ami\Factory;
use Clue\React\Ami\Protocol\Event;
use Clue\React\Ami\Protocol\Response;
use Illuminate\Console\Command;

class AutoCallback extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'callback:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start auto call back on abandon calls';

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
        $masterNo = "2138797457";

        $loop = \React\EventLoop\Factory::create();
        $factory = new Factory($loop);
        $target = 'callback_mgr:0chanc3yo@10.0.0.80';

        $factory->createClient($target)->then(
            function (Client $client) use ($loop) {
                $this->info("Client connected ");

                $sender = new ActionSender($client);
                $sender->events(true);

                $client->on('close', function() {
                    $this->info('Connection closed');
                });
                $client->on('event', function (Event $event) use ($sender, $client) {
                    $this->info('Event: ' . $event->getName() . ': ' . json_encode($event->getFields()));
                    if($event->getName() == 'QueueCallerAbandon') {
                        //$this->info(json_encode($event->getFieldValue('Uniqueid')));
                        $uniqueId = $event->getFieldValue('Uniqueid');

                        // Initiate callback
                        event(new InitiateCallbackEvent($uniqueId));
                    } elseif ($event->getName() == 'OriginateResponse') {
                        $this->info(json_encode($event));
                    }
                });
            },
            function (\Exception $error) {
                $this->info('Connection error: ' . $error->getMessage());
            }
        );

        $loop->run();
        return 0;
    }
}
