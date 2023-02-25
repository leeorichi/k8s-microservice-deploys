<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Junges\Kafka\Facades\Kafka;
use App\Handlers\KafkaHandler;
use Illuminate\Support\Facades\Artisan;

class KafkaConsumer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kafka:consumer-run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kafka Consumer';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        echo "start kafka";

        // $topic = 'k8s-study-svc-topic';
        $topic = 'bizfly-7-442-k8s-study-svc-topic';
        // $group = 'k8s-study-svc-group';
        $group = 'bizfly-7-442-lvt';

        try {
            $consumer = Kafka::createConsumer()
                ->withSasl(new \Junges\Kafka\Config\Sasl(
                    password: 'longha',
                    username: 'bizfly-7-442-longha',
                    mechanisms: 'SCRAM-SHA-512',
                    securityProtocol: 'PLAIN',
                ))
                ->withOptions([
                    'compression.codec' => config('kafka.compression')
                ])
                ->subscribe($topic)
                ->withHandler(new KafkaHandler)
                ->withAutoCommit()
                // ->withConsumerGroupId($group)
                ->build();

            $consumer->consume();
        } catch (\Exception $error) {
            $producer = Kafka::publishOn($topic)
                ->withConfigOption('compression.codec', config('kafka.compression'));

            $producer->send();

            Artisan::call('kafka:consumer-run');
        }
    }
}
