<?php

namespace App\Http\Controllers;

use App\Models\ProductSale;
use \Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductSaleController extends Controller
{
    public function totalSalesInTheLastHour($productId = null): JsonResponse
    {
        $lastHour = Carbon::now()->subHour()->toIso8601String();

        if (empty($productId)) {
            // TODO: put an index on product_id to optimise read. See also
            // comment in ProductRatingController::averageProductRating
            $totalSales = ProductSale::where('created_at', '>=', $lastHour)
                ->get();
        } else {
            $id = (int) trim($productId);
            $product = ProductSale::where('product_id', '=', $id);

            if (empty($product->first())) {
                throw new NotFoundHttpException(
                    message: "No product with id $productId was found",
                    code: Response::HTTP_NOT_FOUND,
                );
            }

            $totalSales = $product->where('created_at', '>=', $lastHour)->get();
        }

        return response()->json([
            'total' => $totalSales->sum('amount')
        ]);
    }
}
