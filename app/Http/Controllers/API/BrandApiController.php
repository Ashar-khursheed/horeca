<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Botble\Base\Events\BeforeEditContentEvent;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Facades\Assets;
use Botble\Base\Supports\Breadcrumb;
use Botble\Ecommerce\Enums\ProductTypeEnum;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Forms\ProductForm;
use Botble\Ecommerce\Http\Requests\ProductRequest;
use Botble\Ecommerce\Models\GroupedProduct;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\Brand;
use Botble\Ecommerce\Models\ProductVariation;
use Botble\Ecommerce\Models\ProductVariationItem;
use Botble\Ecommerce\Services\Products\DuplicateProductService;
use Botble\Ecommerce\Services\Products\StoreAttributesOfProductService;
use Botble\Ecommerce\Services\Products\StoreProductService;
use Botble\Ecommerce\Services\StoreProductTagService;
use Botble\Ecommerce\Tables\ProductTable;
use Botble\Ecommerce\Tables\ProductVariationTable;
use Botble\Ecommerce\Traits\ProductActionsTrait;
use Botble\Ecommerce\Models\Review;

class BrandApiController extends Controller
{

public function getAllBrandProducts(Request $request)
{
    // Fetch all brands
    $brands = Brand::with(['products' => function($query) use ($request) {
        // Apply filters if necessary, similar to the getAllProducts method
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%');
        }
        if ($request->has('price_min')) {
            $query->where('price', '>=', $request->input('price_min'));
        }
        if ($request->has('price_max')) {
            $query->where('price', '<=', $request->input('price_max'));
        }
        if ($request->has('rating')) {
            $rating = $request->input('rating');
            $query->whereHas('reviews', function($q) use ($rating) {
                $q->selectRaw('AVG(star) as avg_rating')
                  ->groupBy('product_id')
                  ->havingRaw('AVG(star) >= ?', [$rating]);
            });
        }
        // Additional filters can be applied as needed
    }])->get();

    // Return the result in a JSON response
    return response()->json([
        'success' => true,
        'data' => $brands->map(function ($brand) {
            return [
                'brand_name' => $brand->name,
                'products' => $brand->products->map(function ($product) {
                    $productArray = $product->toArray();

                    // Add average rating to the product array
                    $productArray['rating'] = $product->reviews()->avg('star'); // Average rating

                    // Return the complete product array
                    return $productArray;
                }),
            ];
        }),
    ]);
}




}
