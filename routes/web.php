<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
use Botble\Ecommerce\Http\Controllers\TempProductController;
use Botble\Ecommerce\Http\Controllers\ProductController;
use Botble\Ecommerce\Http\Controllers\DocumentController;
use Botble\Base\Facades\AdminHelper;
use Botble\Ecommerce\Http\Controllers\ImportProductImageController; // Ensure this path is correct
use Illuminate\Support\Facades\Route;
use Botble\Ecommerce\Http\Controllers\SpecificationController;
use Botble\Ecommerce\Http\Controllers\ImportProductDescriptionController;
use App\Http\Controllers\ShippingController;
use Botble\Ecommerce\Http\Controllers\EliteShipmentController;


Route::get('temp-products', [TempProductController::class, 'index'])->name('temp-products.index');
Route::post('temp-products/approve', [TempProductController::class, 'approveChanges'])->name('temp-products.approve');
Route::post('/delete-document', [DocumentController::class, 'deleteDocument'])
     ->name('document.delete');

     AdminHelper::registerRoutes(function () {
         Route::group(['namespace' => 'Botble\ProductImages\Http\Controllers', 'prefix' => 'ecommerce'], function () {
             Route::group(['prefix' => 'product-images', 'as' => 'product-images.'], function () {
                 Route::get('/import', [ImportProductImageController::class, 'index'])->name('import.index');
                 Route::post('/import', [ImportProductImageController::class, 'store'])->name('import.store');
             });
         });
     });
     Route::post('product-images/import/validate', [ImportProductImageController::class, 'validateImport'])->name('product-images.import.validate');
     Route::post('product-images/import/store', [ImportProductImageController::class, 'storeImport'])->name('product-images.import.store');

// Route::get('/import', [ImportProductImageController::class, 'index'])->name('import.index');
// Route::post('/import', [ImportProductImageController::class, 'store'])->name('import.store');
Route::group(['namespace' => 'Botble\ProductImages\Http\Controllers', 'prefix' => 'ecommerce'], function () {
    Route::group(['prefix' => 'product-images', 'as' => 'product-images.'], function () {
        Route::get('/import', [ImportProductImageController::class, 'index'])->name('import.index');
        Route::post('/import', [ImportProductImageController::class, 'store'])->name('import.store');
    });
});

Route::get('specifications/upload', [SpecificationController::class, 'showUploadForm'])->name('specifications.upload.form');
Route::post('specifications/upload', [SpecificationController::class, 'upload'])->name('specifications.upload');
// Route::group(['middleware' => ['auth']], function () {
//     Route::get('specifications/upload', [SpecificationController::class, 'showUploadForm'])->name('specifications.upload.form');
//     Route::post('specifications/upload', [SpecificationController::class, 'upload'])->name('specifications.upload');
// });


Route::group(['namespace' => 'YourNamespace'], function () {
    Route::get('/products/search-sku', [ProductController::class, 'searchBySku'])->name('products.search-sku');
});

Route::get('/products/search-sku', [ProductController::class, 'searchBySku'])
    ->name('products.search-sku');




    // Define the route for the create form
    Route::get('create-shipment', [EliteShipmentController::class, 'create'])->name('eliteshipment.create');

    // Define the route to handle form submission
    Route::post('store-shipment', [EliteShipmentController::class, 'store'])->name('eliteshipment.store');
