## About Jetstream Take Home

### TECHNICAL DESIGN FOR A BACKEND SYSTEM CAPABLE OF POWERING A REAL-TIME ANALYTICS DASHBOARD FOR A SAAS PLATFORM

### Objective
The goal of this technical design document is to design a backend system capable of powering a real-time analytics dashboard for a Saas platform for a logistics company. Much of the requirements is stated in the “Practical Assessment - Backend Engineering” document.

### System Architecture
![Simple System architecture diagram](system-architecture.png)

A message streaming platform such as Apache Kafka will sit at the heart of this system.
Kafka will receive streams of events from an unspecified number of producers. We’re less worried about the estimated number of producers or messages per day because of Kafka’s ability to handle very large volumes of messages at scale.
A consumer would consume messages from Kafka and create some database records in a NoSQL database such as MongoDB. An analytics REST API written in Laravel will feed from MongoDB to serve the analytics dashboard.
The intention is to have multiple instances of the analytics REST API and consumer running respectively. That way, our system is optimized for availability. With multiple instances of the Analytics REST API running, we introduce a load balancer to ensure that requests are efficiently distributed between all instances of the analytics REST API. 
MongoDB is chosen for a few reasons:
- (a) we’re not sure how often the structure of data by producers could change. We don’t have a strict structure for the data sent to Kafka and we’re unsure of how often new parameters may be added
- (b) we have all the data we need from the data consumed in Kafka. Thus, there’ll most likely be no need for joins to produce analytics data

### Data Ingestion and Processing
Because much of the data for this tech design is simulated, below are a few event streams and the structure of data expected from producers:

#### EVENT 1 (productSold)
This powers analytics such as total sales in the last hour. The idea is that this event is fired by a producer whenever a product is sold. It is ingested by Kafka and processed by a consumer which treats the productSold event and inserts into our NoSQL database (MongoDB).
Structure:
```json
{
    "event_name": "productSold",
    "name_of_product": "product A",
    "product_id": 233,
    "no_of_units": 10,
    "amount":"2500"
}
```

#### EVENT 2 (productRated)
This powers analytics such as average product rating.
Structure:
```json
{
    "event": "productRated",
    "name_of_product": "product A",
    "product_id": 12,
    "rating": 4,
    "category": "F"
}
```

### Security and Compliance
For API authentication, Laravel Sanctum will be used.
In a production grade implementation, we also want to use SSL/TLS to encrypt request/response traffic.
Another consideration is the load balancer sitting in front of the Analytics REST API which serves as a mask for the IP of the REST API.

### Technology Stack
- **Message broker:** Apache Kafka
- **Database:** a NoSQL database such as MongoDB
- **Analytics REST API:** Laravel. This is chosen based on what the team is already familiar with. No learning curve involved.
- **Consumer:** a console command running in the Analytics REST API app


## Installation
- Run `git clone https://github.com/firstCodeOutlaw/jetstream-take-home.git` to clone this repo
- cd into jetstream-take-home directory
- copy .env.example file into .env
- Log on to https://bugsnag.com. Create a free account and get your API key. Use that as the value for the `BUGSNAG_API_KEY` env variable
- Open a terminal and run `./vendor/bin/sail up` to start all Docker containers
- Open another terminal and run `./vendor/bin/sail php artisan key:generate` to generate an app key for the Laravel application
- To start the Kafka consumer, open another terminal and run `./vendor/bin/sail php artisan app:kafka-consume`. It should start listening for messages published to analytics topic in the Dockerized Kafka instance.
- Start a tinker shell in another terminal using `./vendor/bin/sail php artisan tinker`, and run the four commands below to publish productRated and productSold messages to analytics topic:
- ```
  use Junges\Kafka\Facades\Kafka; use Junges\Kafka\Message\Message; $message = new Message(body: ['event_name' => 'productRated', 'name_of_product' => 'Product X', 'product_id' => 1, 'rating' => 4, 'category' => 'Software']); Kafka::publishOn('analytics')->withMessage($message)->send();
  use Junges\Kafka\Facades\Kafka; use Junges\Kafka\Message\Message; $message = new Message(body: ['event_name' => 'productRated', 'name_of_product' => 'Product X', 'product_id' => 1, 'rating' => 5, 'category' => 'Software']); Kafka::publishOn('analytics')->withMessage($message)->send();
  
  use Junges\Kafka\Facades\Kafka; use Junges\Kafka\Message\Message; $message = new Message(body: ['event_name' => 'productSold', 'name_of_product' => 'Product A', 'product_id' => 3, 'no_of_units' => 20, 'amount' => 2000]); Kafka::publishOn('analytics')->withMessage($message)->send();
  use Junges\Kafka\Facades\Kafka; use Junges\Kafka\Message\Message; $message = new Message(body: ['event_name' => 'productSold', 'name_of_product' => 'Product A', 'product_id' => 3, 'no_of_units' => 10, 'amount' => 1000]); Kafka::publishOn('analytics')->withMessage($message)->send();
  ```
- Open Postman or any REST API client of choice to check total products sold in the last hour and average product ratings.
- | Endpoint Name                        | URL                          | HTTP Method | Sample Response         |
  |--------------------------------------|--------------------------------------|-------------|-------------------------|
  | Average Product Ratings              | http://localhost/api/product/rating/1 | GET         | `{ "total": 3000 }`       |
  | Total Product Sales in the Last Hour | http://localhost/api/product/sale/3  | GET         | `{ "average_rating": 4 }` |


## License

The is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
