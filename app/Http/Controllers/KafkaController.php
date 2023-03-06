<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Junges\Kafka\Facades\Kafka;
use Junges\Kafka\Message\Message;


class KafkaController extends Controller
{
    //
    public function sentKafka(string $var = "test")
    {   
        // from study_svc -> long_svc
        $message = [ 
                                'time' => date('Y-m-d H:i:s') ,
                                'mess' => 'HEHE '. $var
                            ] ; 
        self::sendKafkaMessage('k8s-long-svc-topic', json_encode($message));
    }

    
    public static function sendKafkaMessage($topicName, $data)
    {
        // Message initialization
        $message = new Message(
            headers: ['header-key' => 'header-value'],
            body: $data,
            key: time()
        );

        // Publish to broker
        $producer = Kafka::publishOn($topicName)
            ->withConfigOption('compression.codec', config('kafka.compression'))
            ->withMessage($message);

        // Send Message
        $producer->send();
    }
}
