<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProductSaleEndpointTest extends TestCase
{
    use DatabaseMigrations;

    public function test_that_status_is_not_found_if_route_param_is_not_a_number()
    {
        $invalidRouteParam = '3a';
        $response = $this->get("/api/product/sale/$invalidRouteParam");
        $response->assertNotFound();
    }

    public function test_that_status_is_not_found_if_product_id_does_not_exist()
    {
        $productId = 5;
        $response = $this->get("/api/product/sale/$productId");

        $response->assertNotFound();
        $this->assertEquals('{"message":"No product with id 5 was found"}', $response->getContent());
    }

    public function test_that_status_is_200_if_product_exists()
    {
        $productId = 1;
        // Populate test DB with data
        $now = Carbon::now()->toIso8601String();
        DB::table('product_sales')->insert([
            'product_name' => 'Sample product',
            'product_id' => $productId,
            'number_of_units' => 2,
            'amount' => 2000,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Assert
        $response = $this->get("/api/product/sale/$productId");

        $response->assertOk();
        $this->assertEquals('{"total":2000}', $response->getContent());
    }
}
