<?php

namespace App\Http\Controllers;

use App\Models\ProductRating;
use \Illuminate\Http\JsonResponse;

class ProductRatingController extends Controller
{
    public function averageProductRating($productId): JsonResponse
    {
        $id = (int) trim($productId);
        // This is a naive approach for getting average product ratings.
        // A better option would be to calculate average rating per product once,
        // then update the rating per product whenever the Kafka consumer consumes
        // a new ProductRated event. We should emit a productRatingReceived event
        // and attach an event listener that does the actual updating of average
        // ratings for that product. The calculation should be
        // a job so that the consumer thread is not blocked from consuming
        // other messages. This ensures that we can fetch average product rating
        // via this endpoint in O(1) time
        $averageRating = ProductRating::where('product_id', '=', $id)
            ->avg('rating');

        return response()->json([
            'average_rating' => $averageRating
        ]);
    }
}
