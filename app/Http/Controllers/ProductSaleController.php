<?php

namespace App\Http\Controllers;

use App\Models\ProductSale;
use \Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class ProductSaleController extends Controller
{
    public function totalSalesInTheLastHour($productId): JsonResponse
    {
        $id = (int) trim($productId);
        $lastHour = Carbon::now()->subHour();

        // TODO: put an index on product_id to optimise read. See also
        // comment in ProductRatingController::averageProductRating
        $totalSales = ProductSale::where('created_at', '>=', $lastHour);
        $totalSales = empty($productId)
            ? $totalSales->sum('amount')
            : $totalSales->where('product_id', '=', $id)->sum('amount');

        return response()->json([
            'total' => $totalSales
        ]);
    }
}
