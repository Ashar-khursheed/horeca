<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
use App\Http\Controllers\API\CustomerController;
use App\Http\Controllers\API\ProductApiController;
use App\Http\Controllers\API\BrandApiController;
use App\Http\Controllers\API\MenuController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\SimpleSliderController;
use App\Http\Controllers\API\SimpleSliderItemController;
use App\Http\Controllers\API\CategoryApiController;
use App\Http\Controllers\API\ReviewsApiController;
use App\Http\Controllers\API\DiscountsApiController;
use App\Http\Controllers\API\CartApiController;
use App\Http\Controllers\API\PostApiController;
use App\Http\Controllers\API\PostCategoryController;
use App\Http\Controllers\API\CartTotalApiController;
use App\Http\Controllers\API\WishlistApiController;
use App\Http\Controllers\API\CartMultipleProductsApiController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\API\OrderApiController;
Route::get('/location', [LocationController::class, 'getLocation']);
Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);
    Route::post('/', [CategoryController::class, 'index']);
    Route::put('{id}', [CategoryController::class, 'update']);
    Route::delete('{id}', [CategoryController::class, 'destroy']);
    Route::get('{id}', [CategoryController::class, 'show']);
    //Route::middleware('auth:sanctum')->get('/', [CategoryController::class, 'index']);
   // Route::middleware('auth:sanctum')->post('/', [CategoryController::class, 'store']);
    //Route::middleware('auth:sanctum')->put('{id}', [CategoryController::class, 'update']);
    //Route::middleware('auth:sanctum')->delete('{id}', [CategoryController::class, 'destroy']);
    //Route::middleware('auth:sanctum')->get('{id}', [CategoryController::class, 'show']);
    // Route::get('/', [CategoryController::class, 'index']);
    // Route::get('{id}', [CategoryController::class, 'show']);
    // Route::post('/', [CategoryController::class, 'store']);
    // Route::put('{id}', [CategoryController::class, 'update']);
    // Route::delete('{id}', [CategoryController::class, 'destroy']);
});


Route::prefix('/simple-slider')->group(function () {
    Route::get('/', [SimpleSliderController::class, 'index']);
    Route::post('/', [SimpleSliderController::class, 'store']);
    Route::get('{id}', [SimpleSliderController::class, 'show']);
    Route::put('{id}', [SimpleSliderController::class, 'update']);
    Route::delete('{id}', [SimpleSliderController::class, 'destroy']);
});

Route::post('simple-slider-items', [SimpleSliderItemController::class, 'store']);
Route::get('/menus', [MenuController::class, 'index']);
Route::get('/menus/{id}', [MenuController::class, 'show']);
Route::post('/menus', [MenuController::class, 'store']);
Route::put('/menus/{id}', [MenuController::class, 'update']);
Route::delete('/menus/{id}', [MenuController::class, 'destroy']);


Route::post('/register', [CustomerController::class, 'register']);
Route::post('/login', [CustomerController::class, 'login']);

Route::get('/customers', [CustomerController::class, 'index']);
Route::get('/products', [ProductApiController::class, 'getAllProducts']);
Route::get('/brandproducts', [BrandApiController::class, 'getAllBrandProducts']);
Route::get('/categoryproducts', [CategoryApiController::class, 'getAllFeaturedProductsByCategory']);
Route::get('/reviews', [ReviewsApiController::class, 'getProductReviews']);
Route::get('/product-discounts', [DiscountsApiController::class, 'getDiscountsForProduct']);
// Route::put('/profile', [CustomerController::class, 'updateProfile']);
// Route::get('/profile', [CustomerController::class, 'getProfile']);
//Route::middleware('auth:sanctum')->get('/products', [ProductApiController::class, 'getAllProducts']);
// Route::middleware('auth:sanctum')->post('/logout', [CustomerController::class, 'logout']);


//Route::middleware('auth:sanctum')->get('/products', [ProductApiController::class, 'getAllProducts']);
//Route::middleware('auth:sanctum')->post('/login', [CustomerController::class, 'login']);
Route::middleware('auth:sanctum')->put('/profile', [CustomerController::class, 'updateProfile']);
Route::middleware('auth:sanctum')->get('/profile', [CustomerController::class, 'getProfile']);
//Route::middleware('auth:sanctum')->get('/products', [ProductApiController::class, 'getAllProducts']);
Route::middleware('auth:sanctum')->post('/logout', [CustomerController::class, 'logout']);



Route::middleware('auth:sanctum')->group(function () {
    // Routes for logged-in users
    Route::post('/cart', [CartApiController::class, 'addToCart']);
    Route::get('/cart', [CartApiController::class, 'viewCart']);
    Route::delete('/cart', [CartApiController::class, 'clearCart']);
    Route::patch('/cart/decrease', [CartApiController::class, 'decreaseQuantity']); // Decrease quantity for logged-in users
    // Route for updating cart quantity (both user and guest versions)
Route::post('/cart/update', [CartApiController::class, 'updateCartQuantity']);
  Route::get('/cart/total', [CartTotalApiController::class, 'totalProductsInCart']);
   // Route::post('/cart/multiple-add', [CartMultipleProductsApiController::class, 'addMultipleToCart']);

Route::post('/cart/multiple', [CartMultipleProductsApiController::class, 'addMultipleToCart']);

});

Route::post('/cart/guest/multiple', [CartMultipleProductsApiController::class, 'addMultipleToCart']);

// Routes for guest users
Route::post('/cart/guest', [CartApiController::class, 'addToCartGuest']);
Route::get('/cart/guest', [CartApiController::class, 'viewCartGuest']);
Route::delete('/cart/guest', [CartApiController::class, 'clearCartGuest']);
Route::patch('/cart/guest/decrease', [CartApiController::class, 'decreaseQuantityGuest']); // Decrease quantity for guest users
Route::post('/cart/update-guest', [CartApiController::class, 'updateQuantityGuest']);
 Route::get('/cart/total/guest', [CartTotalApiController::class, 'totalProductsInCartGuest']);


//     // Add item to guest cart
//     Route::post('/cart/add-to-cart-guest', [CartApiController::class, 'addToCartGuest'])->name('cart.add.guest');

//     // View guest cart
//     Route::get('/view-cart-guest', [CartApiController::class, 'viewCartGuest'])->name('cart.view.guest');

//     // Update guest cart quantity (decrease)
//     Route::post('/decrease-quantity', [CartApiController::class, 'decreaseQuantity'])->name('cart.decrease.guest');

//     // Clear guest cart
//     Route::delete('/clear-cart-guest', [CartApiController::class, 'clearCartGuest'])->name('cart.clear.guest');


// Routes for Blog Posts 
Route::get('/posts', [PostApiController::class, 'index']);
Route::get('/postcategories', [PostCategoryController::class, 'index']);


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/wishlist/add', [WishlistApiController::class, 'addToWishlist']);
    Route::get('/wishlist', [WishlistApiController::class, 'getWishlist']);
    Route::delete('/wishlist/remove', [WishlistApiController::class, 'removeFromWishlist']); // No productId in the URL
});

// Routes for guest users
Route::middleware('web')->group(function () {

});

    Route::post('wishlist/guest', [WishlistApiController::class, 'addToWishlist']);
    Route::get('wishlist/guest', [WishlistApiController::class, 'getWishlist']);
    //Route::delete('wishlist/guest/{id}', [WishlistApiController::class, 'removeFromWishlist']);
    Route::delete('/wishlist/guest/remove', [WishlistApiController::class, 'removeFromWishlist']); // No productId in the URL





    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/orders', [OrderApiController::class, 'index']);
        Route::post('/orders', [OrderApiController::class, 'store']);
        Route::get('/orders/{id}', [OrderApiController::class, 'show']);
        Route::put('/orders/{id}', [OrderApiController::class, 'update']);
        Route::delete('/orders/{id}', [OrderApiController::class, 'destroy']);
    });
