<?php

namespace App\Handlers;

use Illuminate\Support\Facades\Log;
use Junges\Kafka\Contracts\KafkaConsumerMessage;

class KafkaHandler
{
    public function __invoke(KafkaConsumerMessage $message)
    {
        // Log::debug('Message received!', [
        //     'body' => $message->getBody(),
        //     'headers' => $message->getHeaders(),
        //     'partition' => $message->getPartition(),
        //     'key' => $message->getKey(),
        //     'topic' => $message->getTopicName()
        // ]);

        try {
            $data = json_decode($message->getBody());
            $chanel = "[GLOBAL] ";
            if ($data->to_svc == config("app.name")) {
                $chanel = "[PRIVATE] ";
            }

            $fp = fopen("public/kafka.log", "a+");
                fwrite($fp, $chanel.$message->getBody() . "\n");
                fclose($fp);

        } catch (\Exception $e) {
            Log::channel('kafka')->error($e->getMessage());

            $fp = fopen("public/kafka.log", "a+");
            fwrite($fp, $e->getMessage() . "\n");
            fclose($fp);
        }

        dump($message->getBody());
    }
}
