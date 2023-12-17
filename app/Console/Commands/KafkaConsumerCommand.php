<?php

namespace App\Console\Commands;

use App\Models\ProductRating;
use App\Models\ProductSale;
use Illuminate\Console\Command;
use Junges\Kafka\Contracts\KafkaConsumerMessage;
use Junges\Kafka\Facades\Kafka;

class KafkaConsumerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:kafka-consume';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Consume messages from Kafka';

    private function processMessage(KafkaConsumerMessage $message): void
    {
        $body = $message->getBody();

        switch ($body['event_name']) {
            case 'productRated':
                $productRating = new ProductRating();
                $productRating->product_name = $body['name_of_product'];
                $productRating->product_id = $body['product_id'];
                $productRating->rating = $body['rating'];
                $productRating->category = $body['category'];
                $productRating->save();

                $this->info("Successfully processed a productRated event");
                break;
            case 'productSold':
                $productSale = new ProductSale();
                $productSale->product_name = $body['name_of_product'];
                $productSale->product_id = $body['product_id'];
                $productSale->number_of_units = $body['no_of_units'];
                $productSale->amount = $body['amount'];
                $productSale->save();

                $this->info('Successfully processed a productSold event');
                break;
            default:
                throw new \Exception('Cannot handle unknown event: ' . json_encode($message));
        }
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $consumer = Kafka::createConsumer()
            ->withHandler(function (KafkaConsumerMessage $message) {
                $this->comment("Now consuming: " . json_encode($message->getBody()));
                $this->processMessage($message);
            })
            ->subscribe('analytics')
            ->withDlq()
            ->build();

        $this->info('Kafka consumer initialized...');
        $consumer->consume();
    }
}
