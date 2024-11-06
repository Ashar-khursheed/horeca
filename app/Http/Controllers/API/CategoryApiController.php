<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Botble\Ecommerce\Models\ProductCategory; // Ensure you import the Category model
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\Currency;
class CategoryApiController extends Controller
{
    // public function getAllFeaturedProductsByCategory(Request $request)
    // {
    //     // Fetch all categories with their featured products
    //     $categories = ProductCategory::with(['products' => function($query) use ($request) {
    //         // Filter only products that are featured
    //         $query->where('is_featured', 1);
            
    //         // Apply additional filters if necessary
    //         if ($request->has('search')) {
    //             $query->where('name', 'like', '%' . $request->input('search') . '%');
    //         }
    //         if ($request->has('price_min')) {
    //             $query->where('price', '>=', $request->input('price_min'));
    //         }
    //         if ($request->has('price_max')) {
    //             $query->where('price', '<=', $request->input('price_max'));
    //         }
    //         if ($request->has('rating')) {
    //             $rating = $request->input('rating');
    //             $query->whereHas('reviews', function($q) use ($rating) {
    //                 $q->selectRaw('AVG(star) as avg_rating')
    //                   ->groupBy('product_id')
    //                   ->havingRaw('AVG(star) >= ?', [$rating]);
    //             });
    //         }
    //         // Additional filters can be applied as needed
    //     }])->get();

    //     // Return the result in a JSON response
    //     return response()->json([
    //         'success' => true,
    //         'data' => $categories->map(function ($category) {
    //             return [
    //                 'category_name' => $category->name,
    //                 'featured_products' => $category->products->map(function ($product) {
    //                     $productArray = $product->toArray();

    //                     // Add average rating to the product array
    //                     $productArray['rating'] = $product->reviews()->avg('star'); // Average rating

    //                     // Return the complete product array
    //                     return $productArray;
    //                 }),
    //             ];
    //         }),
    //     ]);
    // }
    
//   public function getAllFeaturedProductsByCategory(Request $request)
// {
//     // Fetch all categories with their featured products
//     $categories = ProductCategory::with(['products' => function($query) use ($request) {
//         $query->where('is_featured', 1);

//         // Apply filters
//         if ($request->has('search')) {
//             $query->where('name', 'like', '%' . $request->input('search') . '%');
//         }
//         if ($request->has('price_min')) {
//             $query->where('price', '>=', $request->input('price_min'));
//         }
//         if ($request->has('price_max')) {
//             $query->where('price', '<=', $request->input('price_max'));
//         }
//         if ($request->has('rating')) {
//             $rating = $request->input('rating');
//             $query->whereHas('reviews', function($q) use ($rating) {
//                 $q->havingRaw('AVG(star) >= ?', [$rating]);
//             });
//         }
//     }])->get();

//     // Limit featured products to 10 per category
//     $categories = $categories->map(function ($category) {
//         return [
//             'category_name' => $category->name,
//             'featured_products' => $category->products->take(10)->map(function ($product) {
//                 $productArray = $product->toArray();
//                 $productArray['rating'] = $product->reviews()->avg('star');
//                 return $productArray;
//             }),
//         ];
//     });

//     return response()->json([
//         'success' => true,
//         'data' => $categories,
//     ]);
// }


// public function getAllFeaturedProductsByCategory(Request $request)
// {
//     // Fetch only the first five featured categories with their featured products
//     $categories = ProductCategory::with(['products' => function($query) use ($request) {
//         $query->where('is_featured', 1);
        
//         // Temporarily remove filters to see available categories
//         /*
//         if ($request->has('search')) {
//             $query->where('name', 'like', '%' . $request->input('search') . '%');
//         }
//         if ($request->has('price_min')) {
//             $query->where('price', '>=', $request->input('price_min'));
//         }
//         if ($request->has('price_max')) {
//             $query->where('price', '<=', $request->input('price_max'));
//         }
//         if ($request->has('rating')) {
//             $rating = $request->input('rating');
//             $query->whereHas('reviews', function($q) use ($rating) {
//                 $q->havingRaw('AVG(star) >= ?', [$rating]);
//             });
//         }
//         */
//     }])
//     ->where('is_featured', 1)
//     ->take(5)
//     ->get();

//     // Limit featured products to 10 per category
//     $categories = $categories->map(function ($category) {
//         return [
//             'category_name' => $category->name,
//             'featured_products' => $category->products->take(10)->map(function ($product) {
//                 $productArray = $product->toArray();
//                 $productArray['rating'] = $product->reviews()->avg('star');
//                 return $productArray;
//             }),
//         ];
//     });

//     return response()->json([
//         'success' => true,
//         'data' => $categories,
//     ]);
// }

// public function getAllFeaturedProductsByCategory(Request $request)
// {
//     // Fetch only the first five categories that have featured products
//     $categories = ProductCategory::whereHas('products', function($query) {
//         $query->where('is_featured', 1); // Ensure there are featured products
//     })
//     ->with(['products' => function($query) {
//         $query->where('is_featured', 1); // Only get featured products
//     }])
//     ->take(5) // Limit to 5 categories
//     ->get();

//     // Map the categories to include featured products
//     $categories = $categories->map(function ($category) {
//         return [
//             'category_name' => $category->name,
//             'featured_products' => $category->products->take(10)->map(function ($product) {
//                 return $product; // Return all product info
//             }),
//         ];
//     });

//     return response()->json([
//         'success' => true,
//         'data' => $categories,
//     ]);
// }


public function getAllFeaturedProductsByCategory(Request $request)
{
    // Fetch only the first five categories that have featured products
    $categories = ProductCategory::whereHas('products', function($query) {
        $query->where('is_featured', 1); // Ensure there are featured products
    })
    ->with(['products' => function($query) {
        $query->where('is_featured', 1); // Only get featured products
    }])
    ->take(5) // Limit to 5 categories
    ->get();

    // Prepare a subquery for best price and delivery date
    $subQuery = Product::select('sku')
        ->selectRaw('MIN(price) as best_price')
        ->selectRaw('MIN(delivery_days) as best_delivery_date')
        ->groupBy('sku');

    // Map the categories to include featured products with additional info
    $categories = $categories->map(function ($category) use ($subQuery) {
        return [
            'category_name' => $category->name,
            'featured_products' => $category->products->take(10)->map(function ($product) use ($subQuery) {
                // Join with the subquery to get best price and delivery date
                $productDetails = Product::leftJoinSub($subQuery, 'best_products', function ($join) {
                    $join->on('ec_products.sku', '=', 'best_products.sku')
                         ->whereColumn('ec_products.price', 'best_products.best_price');
                })
                ->select('ec_products.*', 'best_products.best_price', 'best_products.best_delivery_date')
                ->with('reviews', 'currency')
                ->where('ec_products.id', $product->id) // Only get the current product
                ->first(); // Fetch the product details

                // Count total reviews and calculate average rating
                $totalReviews = $productDetails->reviews->count();
                $avgRating = $totalReviews > 0 ? $productDetails->reviews->avg('star') : null;

                // Calculate left stock
                $quantity = $productDetails->quantity ?? 0;
                $unitsSold = $productDetails->units_sold ?? 0;
                $leftStock = $quantity - $unitsSold;

                // Add currency symbol
                if ($productDetails->currency) {
                    $currencyTitle = $productDetails->currency->title;
                } else {
                    $currencyTitle = $productDetails->price; // Fallback if no currency found
                }

                // Append the values to the product
                // return [
                //     'product' => $productDetails,
                //     'total_reviews' => $totalReviews,
                //     'avg_rating' => $avgRating,
                //     'leftStock' => $leftStock,
                //     'currency' => $currencyTitle,
                // ];
                 // Return product data with additional info
                return array_merge($productDetails->toArray(), [
                    'total_reviews' => $totalReviews,
                    'avg_rating' => $avgRating,
                    'leftStock' => $leftStock,
                    'currency' => $currencyTitle,
                ]);
            }),
        ];
    });

    return response()->json([
        'success' => true,
        'data' => $categories,
    ]);
}


}
