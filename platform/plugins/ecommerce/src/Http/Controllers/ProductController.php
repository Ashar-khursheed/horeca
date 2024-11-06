<?php

namespace Botble\Ecommerce\Http\Controllers;

use Botble\Base\Events\BeforeEditContentEvent;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Facades\Assets;
use Botble\Ecommerce\Models\Specification; // Add this line
use Botble\Base\Supports\Breadcrumb;
use Botble\Ecommerce\Enums\ProductTypeEnum;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Forms\ProductForm;
use Botble\Ecommerce\Http\Requests\ProductRequest;
use Botble\Ecommerce\Models\GroupedProduct;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\Unit;
use Botble\Ecommerce\Models\TempProduct;
use Botble\Ecommerce\Models\ProductVariation;
use Botble\Ecommerce\Models\ProductVariationItem;
use Botble\Ecommerce\Services\Products\DuplicateProductService;
use Botble\Ecommerce\Services\Products\StoreAttributesOfProductService;
use Botble\Ecommerce\Services\Products\StoreProductService;
use Botble\Ecommerce\Services\StoreProductTagService;
use Botble\Ecommerce\Services\StoreProductTypesService;
use Botble\Ecommerce\Tables\ProductTable;
use Botble\Ecommerce\Tables\ProductVariationTable;
use Botble\Ecommerce\Traits\ProductActionsTrait;
use Illuminate\Http\Request;
use Botble\Ecommerce\Models\Review;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;


class ProductController extends BaseController
{
    use ProductActionsTrait;

    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(trans('plugins/ecommerce::products.name'), route('products.index'));
    }

    public function index(ProductTable $dataTable)
    {
        $this->pageTitle(trans('plugins/ecommerce::products.name'));

        Assets::addScripts(['bootstrap-editable'])
            ->addStyles(['bootstrap-editable']);

        return $dataTable->renderTable();
    }

    public function create(Request $request)
    {
        if (EcommerceHelper::isEnabledSupportDigitalProducts()) {
            $this->pageTitle($request->input('product_type') == ProductTypeEnum::DIGITAL
                ? trans('plugins/ecommerce::products.create_product_type.digital')
                : trans('plugins/ecommerce::products.create_product_type.physical'));
        } else {
            $this->pageTitle(trans('plugins/ecommerce::products.create'));
        }

        
        return ProductForm::create()->renderForm();
    }
    public function edit(Product $product, Request $request)
    {
        if ($product->is_variation) {
            abort(404);
        }

        $this->pageTitle(trans('plugins/ecommerce::products.edit', ['name' => $product->name]));

        event(new BeforeEditContentEvent($request, $product));

        return ProductForm::createFromModel($product)->renderForm();
    }
    
    
    public function store(ProductRequest $request,
    StoreProductService $service,
    StoreProductTagService $storeProductTagService ,  StoreProductTypesService $storeProductTypesService  )
    {
        // Get the currently authenticated user
        $user = Auth::user();
    
        // Check if the user has role ID 18 (admin)
        if ($user && DB::table('role_users')->where('user_id', $user->id)->where('role_id', 18)->exists() ) 
        
        {
            // Create a new product instance and save to temp_products for admin approval
            DB::table('temp_products')->insert([
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'handle' => $request->input('handle'),
                'variant_grams' => $request->input('variant_grams'),
                'variant_inventory_tracker' => $request->input('variant_inventory_tracker'),
                'variant_inventory_quantity' => $request->input('variant_inventory_quantity'),
                'variant_inventory_policy' => $request->input('variant_inventory_policy'),
                'variant_fulfillment_service' => $request->input('variant_fulfillment_service'),
                'variant_requires_shipping' => $request->input('variant_requires_shipping', 0),
                'variant_barcode' => $request->input('variant_barcode'),
                'gift_card' => $request->input('gift_card', false),
                'seo_title' => $request->input('seo_title'),
                'seo_description' => $request->input('seo_description'),
                'google_shopping_category' => $request->input('google_shopping_category'),
                // 'google_shopping_gender' => $request->input('google_shopping_gender'),
                // 'google_shopping_age_group' => $request->input('google_shopping_age_group'),
                // 'google_shopping_mpn' => $request->input('google_shopping_mpn'),
                // 'google_shopping_condition' => $request->input('google_shopping_condition'),
                // 'google_shopping_custom_product' => $request->input('google_shopping_custom_product', false),
                // 'google_shopping_custom_label_0' => $request->input('google_shopping_custom_label_0'),
                // 'google_shopping_custom_label_1' => $request->input('google_shopping_custom_label_1'),
                // 'google_shopping_custom_label_2' => $request->input('google_shopping_custom_label_2'),
                // 'google_shopping_custom_label_3' => $request->input('google_shopping_custom_label_3'),
                // 'google_shopping_custom_label_4' => $request->input('google_shopping_custom_label_4'),
                'box_quantity' => $request->input('box_quantity'),
                // 'technical_table' => $request->input('technical_table'),
                // 'technical_spec' => $request->input('technical_spec'),
                'approval_status' => 'pending', // Set default approval status
                'status' => 'published', // Set default approval status
                'created_at' => now(),
                'updated_at' => now(),
            ]);
    
            return $this->httpResponse()
                ->setPreviousUrl(route('products.index'))
                ->withCreatedSuccessMessage();
        } 
        else if ($user && DB::table('role_users')->where('user_id', $user->id)->where('role_id', 10)->exists() )
        {

            // $request->validate([
            //     'documents.*' => 'file|mimes:pdf,doc,docx|max:2048', // Validating the file type and size
                
            // ]);
            // $this->validate($request, [
            //     'documents.*' => 'required|file|mimes:pdf,doc,docx|max:2048',
            // ]);
        
            // $documentsPath = storage_path('app/public/products/documents');
        
            // // Check if the directory exists, if not, create it
            // if (!is_dir($documentsPath)) {
            //     mkdir($documentsPath, 0775, true);
            // }
        
            // $documents = [];
        
            // if ($request->hasFile('documents')) {
            //     foreach ($request->file('documents') as $document) {
            //         // Save each document to storage
            //         $path = $document->store('products/documents', 'public');
            //         $documents[] = $path;
            //     }
            // }

            $this->validate($request, [
                'documents.*' => 'required|file|mimes:pdf,doc,docx|max:2048',
                'titles.*' => 'nullable|string|max:255',
            ]);
            
            $documentsPath = storage_path('app/public/products/documents');
        
            // Check if the directory exists, if not, create it
            if (!is_dir($documentsPath)) {
                mkdir($documentsPath, 0775, true);
            }
        
            $documents = [];
            $fixedTitles = ['Specsheet', 'Manual', 'Warranty', 'Brochure'];
        
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $index => $document) {
                    // Save each document to storage
                    $path = $document->store('products/documents', 'public');
                    
                    // Assign title based on the index
                    if ($index < 4) {
                        $title = $fixedTitles[$index]; // Fixed titles for the first four documents
                    } else {
                        $title = $request->titles[$index] ?: 'Untitled'; // Custom title or default
                    }
        
                    // Store the title and path as an associative array
                    $documents[] = [
                        'title' => $title,
                        'path' => $path,
                    ];
                }
            }

            $request->validate([
                'video_url' => 'nullable|url',
                'video' => 'nullable|mimes:mp4,avi,mov,wmv|max:51200', // Max size of 50 MB
            ]);

            // For users not in role 18, save directly to ec_products
            $product = new Product();
            // $product->name = $request->input('name');
            // $product->description = $request->input('description');
            // $product->description = $request->input('content');
            // $product->handle = $request->input('handle');
            $product->sku= $request->input('sku');
            $product->box_quantity = $request->input('box_quantity');
            // $product->technical_table = $request->input('technical_table');
            // $product->technical_spec = $request->input('technical_spec');
            $product->status = 'published'; // Automatically publish
            $product->documents = json_encode($documents);
            
                        // Handle external video link
            // Handle file upload
            //  if ($request->hasFile('video')) {
            //     $path = $request->file('video')->store('videos', 'public');
            //     $product->video_path = $path;
            //  }
            // Check if a video path already exists and prepare to show it in the view
                $product = Product::find($id);
            if (!$product) {
                return redirect()->back()->with('error', 'Product not found.');
            }

            //               $videoPaths = [];
            //     if ($request->hasFile('videos')) {
            //         foreach ($request->file('videos') as $video) {
            //             $videoPaths[] = $video->store('videos', 'public'); // Store and get the path
            //         }
            //     }
                
            //     // Retrieve existing video paths
            //     $existingVideos = is_string($product->video_path) 
            //         ? json_decode($product->video_path, true) // Decode if it's a JSON string
            //         : (is_array($product->video_path) ? $product->video_path : []); // Already an array
                
            //     // Handle deleted videos
            //     if ($request->has('deleted_videos')) {
            //         $deletedVideos = explode(',', $request->input('deleted_videos'));
            //         $existingVideos = array_filter($existingVideos, fn($video) => !in_array($video, $deletedVideos));
            //     }
                
            //     // Merge with newly uploaded video paths
            //     $allVideos = array_merge($existingVideos, $videoPaths);
            //     $allVideos = array_values(array_unique($allVideos)); // Ensure unique paths
                
            //     // Encode the final paths as JSON
            //     $product->video_path = json_encode($allVideos); // Convert array to JSON string
                
            //     // Save the product with updated video paths
            //     $product->save();
            // $product->save();
                $videoPaths = [];
                
                // Check if there are any uploaded videos
                if ($request->hasFile('videos')) {
                    foreach ($request->file('videos') as $video) {
                        // Store the video and get the path
                        $videoPaths[] = $video->store('videos', 'public'); 
                    }
                }
                
                // Retrieve existing video paths
                $existingVideos = is_string($product->video_path) 
                    ? json_decode($product->video_path, true) // Decode if it's a JSON string
                    : (is_array($product->video_path) ? $product->video_path : []); // Already an array
                
                // Handle deleted videos
                if ($request->has('deleted_videos')) {
                    $deletedVideos = explode(',', $request->input('deleted_videos'));
                
                    // Ensure $existingVideos is an array
                    $existingVideos = $existingVideos ?? [];
                
                    // Filter existing videos
                    $existingVideos = array_filter($existingVideos, fn($video) => !in_array($video, $deletedVideos));
                }
                
                // Merge with newly uploaded video paths
                $allVideos = array_merge($existingVideos, $videoPaths);
                $allVideos = array_values(array_unique($allVideos)); // Ensure unique paths
                
                // Encode the final paths as JSON
                $product->video_path = json_encode($allVideos, JSON_UNESCAPED_SLASHES); // Convert array to JSON string without escaping slashes
                
                // Save the product with updated video paths
                $product->save();

            // Return to the form with existing data
            return view('plugins/ecommerce::products.partials.video-upload', [
                'product' => $product,
            ]);
    
         // Additional processing
            $product->status = $request->input('status');
            if (EcommerceHelper::isEnabledSupportDigitalProducts() && $productType = $request->input('product_type')) {
                $product->product_type = $productType;
            }
            $product = $service->execute($request, $product);

            $storeProductTagService->execute($request, $product);
            $storeProductTypesService->execute($request, $product);
        
    
  


            // Handle product variations and attributes
            $addedAttributes = $request->input('added_attributes', []);
            if ($request->input('is_added_attributes') == 1 && $addedAttributes) {
                // $storeAttributesOfProductService->execute(
                //     $product,
                //     array_keys($addedAttributes),
                //     array_values($addedAttributes)
                // );
        
                $variation = ProductVariation::query()->create([
                    'configurable_product_id' => $product->id,
                ]);
        
                new CreatedContentEvent(PRODUCT_VARIATIONS_MODULE_SCREEN_NAME, request(), $variation);
        
                foreach ($addedAttributes as $attribute) {
                    ProductVariationItem::query()->create([
                        'attribute_id' => $attribute,
                        'variation_id' => $variation->id,
                    ]);
                }
        
                $variation = $variation->toArray();
                $variation['variation_default_id'] = $variation['id'];
                $variation['sku'] = $product->sku;
                $variation['auto_generate_sku'] = true;
                $variation['images'] = array_filter((array) $request->input('images', []));
        
                $this->postSaveAllVersions(
                    [$variation['id'] => $variation],
                    $product->id,
                    $this->httpResponse()
                );
            }
        
            // Handle grouped products
            if ($request->has('grouped_products')) {
                GroupedProduct::createGroupedProducts(
                    $product->id,
                    array_map(function ($item) {
                        return [
                            'id' => $item,
                            'qty' => 1,
                        ];
                    }, array_filter(explode(',', $request->input('grouped_products', ''))))
                );
            }

            $this->saveSpecifications($product, $request->specs);
        
            
                $product->save();
        
            
                    // Return success response
                    return $this->httpResponse()
                        ->setPreviousUrl(route('products.index'))
                        ->setNextUrl(route('products.edit', $product->id))
                        ->withCreatedSuccessMessage();
            }
        
        else {
            $this->validate($request, [
                'documents.*' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
                'titles.*' => 'nullable|string|max:255',
            ]);
            
             $validatedData = $request->validate([
                'compare_type' => 'nullable|string',
                'compare_products' => 'nullable|string',
            ]);
        
         

            // For users not in role 18, save directly to ec_products
            $product = new Product();
            $product->name = $request->input('name');
            $product->description = $request->input('description');
            $product->content = $request->input('content');
            $product->handle = $request->input('handle');
            $product->handle = $request->input('delivery_days');
            $product->variant_requires_shipping = $request->input('variant_requires_shipping');
            // $product->refund_policy = $request->input('refund_policy');
            $product->google_shopping_category = $request->input('google_shopping_category');
            $product->unit_of_measurement_id = $request->input('unit_of_measurement_id');
            // Update compare_type and compare_products
          $product->compare_type = json_encode(explode(',', $validatedData['compare_type']));
          $product->compare_products = json_encode(explode(',', $validatedData['compare_products']));
            $product->frequently_bought_together = $request->input('frequently_bought_together');
            
             
            $product->variant_color_title = "Color";
            $product->variant_color_value = $request->input('variant_color_value');
            $product->variant_color_products = $request->input('variant_color_products');
            
            $product->variant_1_title = $request->input('variant_1_title');
            $product->variant_1_value = $request->input('variant_1_value');
            $product->variant_1_products = $request->input('variant_1_products');
        
            $product->variant_2_title = $request->input('variant_2_title');
            $product->variant_2_value = $request->input('variant_2_value');
            $product->variant_2_products = $request->input('variant_2_products');
        
            $product->variant_3_title = $request->input('variant_3_title');
            $product->variant_3_value = $request->input('variant_3_value');
            $product->variant_3_products = $request->input('variant_3_products');
            $product->shipping_weight= $request->input('shipping_weight');
            
            $product->shipping_weight= $request->input('shipping_weight');
            $allowedOptions = ['Kg', 'g', 'pounds', 'oz'];
                    $shippingWeightOption = $request->input('shipping_weight_option');

                    // Check if the provided option is valid
                    if (in_array($shippingWeightOption, $allowedOptions)) {
                        $product->shipping_weight_option = $shippingWeightOption;
                    } else {
                        // Handle the case where the input is invalid
                        return response()->json(['error' => 'Invalid shipping weight option.'], 400);
                    }
            $product->refund= $request->input('refund');
            
               // Load existing documents
            $existingDocuments = json_decode($product->documents, true) ?? [];
        
            $documentsPath = storage_path('app/public/products/documents');
        
            // Check if the directory exists, if not, create it
            if (!is_dir($documentsPath)) {
                mkdir($documentsPath, 0775, true);
            }
        
            // Merge existing documents with new ones
            $documents = $existingDocuments;
        
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $index => $document) {
                    $path = $document->store('products/documents', 'public');
        
                    // Assign title based on the index
                    // if ($index < 4) {
                    //     $title = 'Document ' . ($index + 1); // Fixed titles for the first four documents
                    // } else {
                    //     $title = $request->titles[$index] ?: 'Untitled'; // Custom title or default
                    // }
                    $titles = [
                        'Specsheet',
                        'Manual',
                        'Warranty',
                        'Brochure',
                    ];
                    // Determine the title for the document
                    
                    if ($index < count($titles)) {
                        $title = $titles[$index]; // Fetch title from the array
                    } 
                    else {
                            $title = $request->titles[$index] ?: 'Untitled'; // Custom title or default
                        }
                    // Store the title and path as an associative array
                    $documents[] = [
                        'title' => $title,
                        'path' => $path,
                    ];
                }
            }
        
            // Save the documents as JSON in the product
            $product->documents = json_encode($documents);
            $request->validate([
                'video_url' => 'nullable|url',
                'video' => 'nullable|mimes:mp4,avi,mov,wmv|max:51200', // Max size of 50 MB
            ]);
           
            // $product->variant_grams = $request->input('variant_grams');
            // $product->variant_inventory_tracker = $request->input('variant_inventory_tracker');
            // $product->variant_inventory_quantity = $request->input('variant_inventory_quantity');
            // $product->variant_inventory_policy = $request->input('variant_inventory_policy');
            // $product->variant_fulfillment_service = $request->input('variant_fulfillment_service');
            // $product->variant_requires_shipping = $request->input('variant_requires_shipping', 0);
            // $product->variant_barcode = $request->input('variant_barcode');
            // $product->gift_card = $request->input('gift_card', false);
            // $product->seo_title = $request->input('seo_title');
            // $product->seo_description = $request->input('seo_description');
            // $product->google_shopping_category = $request->input('google_shopping_category');
            // $product->google_shopping_gender = $request->input('google_shopping_gender');
            // $product->google_shopping_age_group = $request->input('google_shopping_age_group');
            // $product->google_shopping_mpn = $request->input('google_shopping_mpn');
            // $product->google_shopping_condition = $request->input('google_shopping_condition');
            // $product->google_shopping_custom_product = $request->input('google_shopping_custom_product', false);
            // $product->google_shopping_custom_label_0 = $request->input('google_shopping_custom_label_0');
            // $product->google_shopping_custom_label_1 = $request->input('google_shopping_custom_label_1');
            // $product->google_shopping_custom_label_2 = $request->input('google_shopping_custom_label_2');
            // $product->google_shopping_custom_label_3 = $request->input('google_shopping_custom_label_3');
            // $product->google_shopping_custom_label_4 = $request->input('google_shopping_custom_label_4');
            $product->box_quantity = $request->input('box_quantity');
            // $product->technical_table = $request->input('technical_table');
            // $product->technical_spec = $request->input('technical_spec');
            $product->status = 'published'; // Automatically publish
          //  $product->documents = json_encode($documents);
                        // Handle external video link
         // Handle file upload
        //  if ($request->hasFile('video')) {
        //     $path = $request->file('video')->store('videos', 'public');
        //     $product->video_path = $path;
        // }
        
        //  $videoPaths = [];
        //     if ($request->hasFile('videos')) {
        //         foreach ($request->file('videos') as $video) {
        //             $videoPaths[] = $video->store('videos', 'public');
        //         }
        //     }

        //     // Merge with existing videos if necessary
        //     $existingVideos = $product->video_path ? json_decode($product->video_path) : [];
        //     $allVideos = array_merge($existingVideos, $videoPaths);

        //     // Save the video paths as JSON
        //     $product->video_path = json_encode($allVideos);
        //     $product->save();
        
                //         $videoPaths = [];
                // if ($request->hasFile('videos')) {
                //     foreach ($request->file('videos') as $video) {
                //         $videoPaths[] = $video->store('videos', 'public'); // Store and get the path
                //     }
                // }
                
                // // Retrieve existing video paths
                // $existingVideos = is_string($product->video_path) 
                //     ? json_decode($product->video_path, true) // Decode if it's a JSON string
                //     : (is_array($product->video_path) ? $product->video_path : []); // Already an array
                
                // // Handle deleted videos
                // if ($request->has('deleted_videos')) {
                //     $deletedVideos = explode(',', $request->input('deleted_videos'));
                //     $existingVideos = array_filter($existingVideos, fn($video) => !in_array($video, $deletedVideos));
                // }
                
                // // Merge with newly uploaded video paths
                // $allVideos = array_merge($existingVideos, $videoPaths);
                // $allVideos = array_values(array_unique($allVideos)); // Ensure unique paths
                
                // // Encode the final paths as JSON
                // $product->video_path = json_encode($allVideos); // Convert array to JSON string
                
                // // Save the product with updated video paths
                // $product->save();
            
            
                            $videoPaths = [];
                
                // Check if there are any uploaded videos
                if ($request->hasFile('videos')) {
                    foreach ($request->file('videos') as $video) {
                        // Store the video and get the path
                        $videoPaths[] = $video->store('videos', 'public'); 
                    }
                }
                
                // Retrieve existing video paths
                $existingVideos = is_string($product->video_path) 
                    ? json_decode($product->video_path, true) // Decode if it's a JSON string
                    : (is_array($product->video_path) ? $product->video_path : []); // Already an array
                
                // Handle deleted videos
                if ($request->has('deleted_videos')) {
                    $deletedVideos = explode(',', $request->input('deleted_videos'));
                
                    // Ensure $existingVideos is an array
                    $existingVideos = $existingVideos ?? [];
                
                    // Filter existing videos
                    $existingVideos = array_filter($existingVideos, fn($video) => !in_array($video, $deletedVideos));
                }
                
                // Merge with newly uploaded video paths
                $allVideos = array_merge($existingVideos, $videoPaths);
                $allVideos = array_values(array_unique($allVideos)); // Ensure unique paths
                
                // Encode the final paths as JSON
                $product->video_path = json_encode($allVideos, JSON_UNESCAPED_SLASHES); // Convert array to JSON string without escaping slashes
                
                // Save the product with updated video paths
                $product->save();

        $product->weight_unit_id = $request['weight_unit_id'];
        $product->length_unit_id = $request['length_unit_id'];
        $product->depth_unit_id = $request['depth_unit_id'];
        $product->height_unit_id = $request['height_unit_id'];
        $product->width_unit_id = $request['width_unit_id'];
        $product->shipping_length_id = $request['shipping_length_id'];
        $product->shipping_depth_id = $request['shipping_depth_id'];
        $product->shipping_height_id = $request['shipping_height_id'];
        $product->shipping_width_id = $request['shipping_width_id'];
        // Additional processing
        $product->status = $request->input('status');
        if (EcommerceHelper::isEnabledSupportDigitalProducts() && $productType = $request->input('product_type')) {
            $product->product_type = $productType;
        }
        $product = $service->execute($request, $product);

        $storeProductTagService->execute($request, $product);
     
        $storeProductTypesService->execute($request, $product);
  


        // Handle product variations and attributes
        $addedAttributes = $request->input('added_attributes', []);
        if ($request->input('is_added_attributes') == 1 && $addedAttributes) {
            $storeAttributesOfProductService->execute(
                $product,
                array_keys($addedAttributes),
                array_values($addedAttributes)
            );
    
            $variation = ProductVariation::query()->create([
                'configurable_product_id' => $product->id,
            ]);
    
            new CreatedContentEvent(PRODUCT_VARIATIONS_MODULE_SCREEN_NAME, request(), $variation);
    
            foreach ($addedAttributes as $attribute) {
                ProductVariationItem::query()->create([
                    'attribute_id' => $attribute,
                    'variation_id' => $variation->id,
                ]);
            }
    
            $variation = $variation->toArray();
            $variation['variation_default_id'] = $variation['id'];
            $variation['sku'] = $product->sku;
            $variation['auto_generate_sku'] = true;
            $variation['images'] = array_filter((array) $request->input('images', []));
    
            $this->postSaveAllVersions(
                [$variation['id'] => $variation],
                $product->id,
                $this->httpResponse()
            );
        }
    
        // Handle grouped products
        if ($request->has('grouped_products')) {
            GroupedProduct::createGroupedProducts(
                $product->id,
                array_map(function ($item) {
                    return [
                        'id' => $item,
                        'qty' => 1,
                    ];
                }, array_filter(explode(',', $request->input('grouped_products', ''))))
            );
        }

        $this->saveSpecifications($product, $request->specs);
     
        
            $product->save();
    
         
                // Return success response
                return $this->httpResponse()
                    ->setPreviousUrl(route('products.index'))
                    ->setNextUrl(route('products.edit', $product->id))
                    ->withCreatedSuccessMessage();
        }
    }
    

    
    
    
    public function update($id,  
    ProductRequest $request,
    StoreProductService $service,
    StoreProductTagService $storeProductTagService ,   StoreProductTypesService $storeProductTypesService )
    {


        
        // Get the currently authenticated user
        $user = Auth::user();
        
        // Check if the product exists in ec_products
        $product = Product::find($id);
        $tempproduct = TempProduct::find($id);
        if (!$product) {
            return redirect()->back()->with('error', 'Product not found.');
        }
        $request->validate([
            'video_url' => 'nullable|url',
            'video' => 'nullable|mimes:mp4,avi,mov,wmv|max:51200', // Max size of 50 MB
        ]);
        
       
        // Check if the user has role ID 18 (user role)
        if ($user && DB::table('role_users')->where('user_id', $user->id)->where('role_id',18)->exists()) {
            // Create a copy in temp_products
            DB::table('temp_products')->insert([
                'product_id' => $product->id, // Foreign key reference to ec_products
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'content' => $request->input('content'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
    
            return redirect()->route('products.index')->with('success', 'Product update request submitted and saved for approval.');
        } 

          
        // Check if the user has role ID 18 (user role)
        if ($user && DB::table('role_users')->where('user_id', $user->id)->where('role_id',19)->exists()){
            // Update the product directly in ec_products

            // $request->validate([
            //     'documents.*' => 'file|mimes:pdf,doc,docx|max:2048',
            // ]);
        
            // $documents = json_decode($product->documents, true) ?? [];
        
            // if ($request->hasFile('documents')) {
            //     foreach ($request->file('documents') as $document) {
            //         $path = $document->store('products/documents', 'public');
            //         $documents[] = $path;
            //     }
            // }

            // $this->validate($request, [
            //     'documents.*' => 'required|file|mimes:pdf,doc,docx|max:2048',
            //     'titles.*' => 'nullable|string|max:255',
            // ]);
            
            // $documentsPath = storage_path('app/public/products/documents');
        
            // // Check if the directory exists, if not, create it
            // if (!is_dir($documentsPath)) {
            //     mkdir($documentsPath, 0775, true);
            // }
        
            // $documents = [];
            // $fixedTitles = ['Document 1', 'Document 2', 'Document 3', 'Document 4'];
        
            // if ($request->hasFile('documents')) {
            //     foreach ($request->file('documents') as $index => $document) {
            //         // Save each document to storage
            //         $path = $document->store('products/documents', 'public');
                    
            //         // Assign title based on the index
            //         if ($index < 4) {
            //             $title = $fixedTitles[$index]; // Fixed titles for the first four documents
            //         } else {
            //             $title = $request->titles[$index] ?: 'Untitled'; // Custom title or default
            //         }
        
            //         // Store the title and path as an associative array
            //         $documents[] = [
            //             'title' => $title,
            //             'path' => $path,
            //         ];
            //     }
            // }
        
            // $product->documents = json_encode($documents);

              // Validate the incoming request
                // $this->validate($request, [
                //     'documents.*' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
                //     'titles.*' => 'nullable|string|max:255',
                // ]);

                // // Load existing documents
                // $existingDocuments = json_decode($product->documents, true) ?? [];

                // $documentsPath = storage_path('app/public/products/documents');

                // // Check if the directory exists, if not, create it
                // if (!is_dir($documentsPath)) {
                //     mkdir($documentsPath, 0775, true);
                // }

                // // Initialize documents array with existing documents
                // $documents = $existingDocuments;

                // // Handle new document uploads
                // if ($request->hasFile('documents')) {
                //     foreach ($request->file('documents') as $index => $document) {
                //         // Store the new document
                //         $path = $document->store('products/documents', 'public');

                //         // Determine the title for the document
                //         if ($index < 4) {
                //             $title = 'Document ' . ($index + 1); // Fixed titles for the first four documents
                //         } else {
                //             $title = $request->titles[$index] ?? 'Untitled'; // Custom title or default
                //         }

                //         // Add the new document to the array
                //         $documents[] = [
                //             'title' => $title,
                //             'path' => $path,
                //         ];
                //     }
                // }

                // // Save the updated documents as JSON in the product
                // $product->documents = json_encode($documents);


                // Validate the incoming request
                $this->validate($request, [
                    'documents.*' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
                    'titles.*' => 'nullable|string|max:255',
                ]);

                // Load existing documents
                $existingDocuments = json_decode($product->documents, true) ?? [];

                $documentsPath = storage_path('app/public/products/documents');

                // Check if the directory exists, if not, create it
                if (!is_dir($documentsPath)) {
                    mkdir($documentsPath, 0775, true);
                }

                // Initialize documents array with existing documents
                $documents = $existingDocuments;

                // Handle new document uploads
                if ($request->hasFile('documents')) {
                    foreach ($request->file('documents') as $index => $document) {
                        // Overwrite existing document if the index is within the range of existing documents
                        if ($index < count($documents)) {
                            // Delete the old file if it exists
                            if (file_exists(storage_path('app/public/' . $documents[$index]['path']))) {
                                unlink(storage_path('app/public/' . $documents[$index]['path']));
                            }
                        } else {
                            // If it’s a new document upload, create an empty array for it
                            $documents[$index] = [];
                        }

                        // Store the new document
                        $path = $document->store('products/documents', 'public');

                        // Determine the title for the document
                        if ($index < 4) {
                            $title = 'Document ' . ($index + 1); // Fixed titles for the first four documents
                        } else {
                            $title = $request->titles[$index] ?? 'Untitled'; // Custom title or default
                        }

                        // Update the document details
                        $documents[$index] = [
                            'title' => $title,
                            'path' => $path,
                        ];
                    }
                }

                // Save the updated documents as JSON in the product
                $product->documents = json_encode($documents);
                // $product->handle = $request->input('handle');
                // $product->variant_grams = $request->input('variant_grams');
                // $product->variant_inventory_tracker = $request->input('variant_inventory_tracker');
                // $product->variant_inventory_quantity = $request->input('variant_inventory_quantity');
                // $product->variant_inventory_policy = $request->input('variant_inventory_policy');
                // $product->variant_fulfillment_service = $request->input('variant_fulfillment_service');
            
                // $product->variant_barcode = $request->input('variant_barcode');
                // $product->gift_card = $request->input('gift_card');
                // $product->seo_title = $request->input('seo_title');
                // $product->seo_description = $request->input('seo_description');
             
               

               
            
             // Decode the JSON data for compare_type and compare_products
                // $compareTypes = $product->compare_type ? json_decode($product->compare_type, true) : [];
                // $compareProducts = $product->compare_products ? json_decode($product->compare_products, true) : [];

            
            
                        $product->frequently_bought_together = $request->input('frequently_bought_together');
            
                // $product->google_shopping_gender = $request->input('google_shopping_gender');
                // $product->google_shopping_age_group = $request->input('google_shopping_age_group');
                $product->google_shopping_mpn = $request->input('google_shopping_mpn');
                // $product->google_shopping_condition = $request->input('google_shopping_condition');
                // $product->google_shopping_custom_product = $request->input('google_shopping_custom_product');
                // $product->google_shopping_custom_label_0 = $request->input('google_shopping_custom_label_0');
                // $product->google_shopping_custom_label_1 = $request->input('google_shopping_custom_label_1');
                // $product->google_shopping_custom_label_2 = $request->input('google_shopping_custom_label_2');
                // $product->google_shopping_custom_label_3 = $request->input('google_shopping_custom_label_3');
                // $product->google_shopping_custom_label_4 = $request->input('google_shopping_custom_label_4');
                $product->box_quantity = $request->input('box_quantity');
                // $product->technical_table = $request->input('technical_table');
                // $product->technical_spec = $request->input('technical_spec');
                $product->minimum_order_quantity = $request->input('minimum_order_quantity');
                $product->maximum_order_quantity = $request->input('maximum_order_quantity');
                $product->quantity = $request->input('quantity');
                // if ($request->filled('video_url')) {
                //     $product->video_url = $request->video_url;
                // }

                // Handle file upload
                // if ($request->hasFile('video')) {
                //     $path = $request->file('video')->store('videos', 'public');
                //     $product->video_path = $path;
                // }

        
                // Additional processing
                $product->status = $request->input('status');
                if (EcommerceHelper::isEnabledSupportDigitalProducts() && $productType = $request->input('product_type')) {
                    $product->product_type = $productType;
                }
                $product = $service->execute($request, $product);

                $storeProductTagService->execute($request, $product);
            
                $storeProductTypesService->execute($request, $product);
        
        
        
                // Handle product variations and attributes
                $addedAttributes = $request->input('added_attributes', []);
                if ($request->input('is_added_attributes') == 1 && $addedAttributes) {
                    $storeAttributesOfProductService->execute(
                    $product,
                    array_keys($addedAttributes),
                    array_values($addedAttributes)
                );
        
                $variation = ProductVariation::query()->create([
                    'configurable_product_id' => $product->id,
                ]);
        
                new CreatedContentEvent(PRODUCT_VARIATIONS_MODULE_SCREEN_NAME, request(), $variation);
        
                foreach ($addedAttributes as $attribute) {
                    ProductVariationItem::query()->create([
                        'attribute_id' => $attribute,
                        'variation_id' => $variation->id,
                    ]);
                }
        
                $variation = $variation->toArray();
                $variation['variation_default_id'] = $variation['id'];
                $variation['sku'] = $product->sku;
                $variation['auto_generate_sku'] = true;
                $variation['images'] = array_filter((array) $request->input('images', []));
        
                $this->postSaveAllVersions(
                    [$variation['id'] => $variation],
                    $product->id,
                    $this->httpResponse()
                );
            }
        
            // Handle grouped products
            if ($request->has('grouped_products')) {
                GroupedProduct::createGroupedProducts(
                    $product->id,
                    array_map(function ($item) {
                        return [
                            'id' => $item,
                            'qty' => 1,
                        ];
                    }, array_filter(explode(',', $request->input('grouped_products', ''))))
                );
            }

            $this->saveSpecifications($product, $request->specs);
        
            // Return success response
            return $this->httpResponse()
                ->setPreviousUrl(route('products.index'))
                ->setNextUrl(route('products.edit', $product->id))
                ->withUpdatedSuccessMessage();
        }
        
      
        
        
    
          else {
                    // Validate incoming request data
                    $this->validate($request, [
                        'documents.*' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
                        'titles.*' => 'nullable|string|max:255',
                        'compare_type' => 'nullable|string',
                        'compare_products' => 'nullable|string',
                    ]);
                
                    // Load existing documents if any
                    $existingDocuments = json_decode($product->documents, true) ?? [];
                    $documentsPath = storage_path('app/public/products/documents');
                
                    // Ensure documents directory exists
                    if (!is_dir($documentsPath)) {
                        mkdir($documentsPath, 0775, true);
                    }
                
                    $documents = $existingDocuments;
                
                    // Handle new document uploads
                    if ($request->hasFile('documents')) {
                        $titles = ['Specsheet', 'Manual', 'Warranty', 'Brochure'];
                
                        foreach ($request->file('documents') as $index => $document) {
                            // If existing document, remove old file
                            if ($index < count($documents)) {
                                if (file_exists(storage_path('app/public/' . $documents[$index]['path']))) {
                                    unlink(storage_path('app/public/' . $documents[$index]['path']));
                                }
                            }
                
                            // Save new document
                            $path = $document->store('products/documents', 'public');
                            $title = $titles[$index] ?? ($request->titles[$index] ?? 'Untitled');
                
                            // Update document details
                            $documents[$index] = [
                                'title' => $title,
                                'path' => $path,
                            ];
                        }
                    }
                    $product->documents = json_encode($documents);
                
                 
                    // $videoPaths = [];
                    // if ($request->hasFile('videos')) {
                    //     foreach ($request->file('videos') as $video) {
                    //         $videoPaths[] = $video->store('videos', 'public');
                    //     }
                    // }
                
                    // // $existingVideos = json_decode($product->video_path, true) ?? [];
                    //   $existingVideos = is_string($product->video_path) 
                    // ? json_decode($product->video_path, true) ?? [] 
                    // : (is_array($product->video_path) ? $product->video_path : []);
                
                    //                 if ($request->has('deleted_videos')) {
                    //     $deletedVideos = explode(',', $request->input('deleted_videos'));
                    //     $existingVideos = array_filter($existingVideos, fn($video) => !in_array($video, $deletedVideos));
                    // }
                    // $allVideos = array_merge($existingVideos, $videoPaths);
                    // $product->video_path = json_encode($allVideos);
                
                //                 $videoPaths = [];
                // if ($request->hasFile('videos')) {
                //     foreach ($request->file('videos') as $video) {
                //         $videoPaths[] = $video->store('videos', 'public'); // Store and get the path
                //     }
                // }
                
                // // Retrieve existing video paths
                // $existingVideos = is_string($product->video_path) 
                //     ? json_decode($product->video_path, true) // Decode if it's a JSON string
                //     : (is_array($product->video_path) ? $product->video_path : []); // Already an array
                
                // // Handle deleted videos
                // // if ($request->has('deleted_videos')) {
                // //     $deletedVideos = explode(',', $request->input('deleted_videos'));
                // //     $existingVideos = array_filter($existingVideos, fn($video) => !in_array($video, $deletedVideos));
                // // }
                // if ($request->has('deleted_videos')) {
                //     $deletedVideos = explode(',', $request->input('deleted_videos'));
                
                //     // Ensure $existingVideos is an array
                //     $existingVideos = $existingVideos ?? [];
                
                //     // Filter existing videos
                //     $existingVideos = array_filter($existingVideos, fn($video) => !in_array($video, $deletedVideos));
                // }

                // // Merge with newly uploaded video paths
                // $allVideos = array_merge($existingVideos, $videoPaths);
                // $allVideos = array_values(array_unique($allVideos)); // Ensure unique paths
                
                // // Encode the final paths as JSON
                // $product->video_path = json_encode($allVideos); // Convert array to JSON string
                
                // // Save the product with updated video paths
                // $product->save();
                
                $videoPaths = [];

            // Check if there are any uploaded videos
            if ($request->hasFile('videos')) {
                foreach ($request->file('videos') as $video) {
                    // Store the video and get the path
                    $videoPaths[] = $video->store('videos', 'public'); 
                }
            }
            
            // Retrieve existing video paths
            $existingVideos = is_string($product->video_path) 
                ? json_decode($product->video_path, true) // Decode if it's a JSON string
                : (is_array($product->video_path) ? $product->video_path : []); // Already an array
            
            // Handle deleted videos
            if ($request->has('deleted_videos')) {
                $deletedVideos = explode(',', $request->input('deleted_videos'));
            
                // Ensure $existingVideos is an array
                $existingVideos = $existingVideos ?? [];
            
                // Filter existing videos
                $existingVideos = array_filter($existingVideos, fn($video) => !in_array($video, $deletedVideos));
            }
            
            // Merge with newly uploaded video paths
            $allVideos = array_merge($existingVideos, $videoPaths);
            $allVideos = array_values(array_unique($allVideos)); // Ensure unique paths
            
            // Encode the final paths as JSON
            $product->video_path = json_encode($allVideos, JSON_UNESCAPED_SLASHES); // Convert array to JSON string without escaping slashes
            
            // Save the product with updated video paths
            $product->save();


                    // Update additional fields
                    $product->variant_requires_shipping = $request->input('variant_requires_shipping');
                    $product->refund = $request->input('refund');
                    $product->shipping_weight = $request->input('shipping_weight');
                    
                    $allowedOptions = ['Kg', 'g', 'pounds', 'oz'];
                    $shippingWeightOption = $request->input('shipping_weight_option');
                    if (in_array($shippingWeightOption, $allowedOptions)) {
                        $product->shipping_weight_option = $shippingWeightOption;
                    } else {
                        return response()->json(['error' => 'Invalid shipping weight option.'], 400);
                    }
                
                    // Save additional attributes
                    $product->variant_color_title = "Color";
                    $product->variant_color_value = $request->input('variant_color_value');
                    $product->variant_color_products = $request->input('variant_color_products');
                    $product->variant_1_title = $request->input('variant_1_title');
                    $product->variant_1_value = $request->input('variant_1_value');
                    $product->variant_1_products = $request->input('variant_1_products');
                    $product->variant_2_title = $request->input('variant_2_title');
                    $product->variant_2_value = $request->input('variant_2_value');
                    $product->variant_2_products = $request->input('variant_2_products');
                    $product->variant_3_title = $request->input('variant_3_title');
                    $product->variant_3_value = $request->input('variant_3_value');
                    $product->variant_3_products = $request->input('variant_3_products');
                
                    // Additional properties and Google Shopping fields
                    $product->google_shopping_category = $request->input('google_shopping_category');
                    $product->unit_of_measurement_id = $request->input('unit_of_measurement_id');
                    $product->weight_unit_id = $request->input('weight_unit_id');
                    $product->length_unit_id = $request->input('length_unit_id');
                    $product->depth_unit_id = $request->input('depth_unit_id');
                    $product->height_unit_id = $request->input('height_unit_id');
                    $product->width_unit_id = $request->input('width_unit_id');
                    $product->shipping_length_id = $request->input('shipping_length_id');
                    $product->shipping_depth_id = $request->input('shipping_depth_id');
                    $product->shipping_height_id = $request->input('shipping_height_id');
                    $product->shipping_width_id = $request->input('shipping_width_id');
                    $product->compare_type = json_encode(explode(',', $request->input('compare_type')));
                    $product->compare_products = json_encode(explode(',', $request->input('compare_products')));
                    $product->frequently_bought_together = $request->input('frequently_bought_together');
                    $product->google_shopping_mpn = $request->input('google_shopping_mpn');
                    $product->box_quantity = $request->input('box_quantity');
                
                    $product->save();



        
                // Additional processing
                $product->status = $request->input('status');
                if (EcommerceHelper::isEnabledSupportDigitalProducts() && $productType = $request->input('product_type')) {
                    $product->product_type = $productType;
                }
                $product = $service->execute($request, $product);

                $storeProductTagService->execute($request, $product);
            
                $storeProductTypesService->execute($request, $product);
        
        
        
                // Handle product variations and attributes
                $addedAttributes = $request->input('added_attributes', []);
                if ($request->input('is_added_attributes') == 1 && $addedAttributes) {
                    $storeAttributesOfProductService->execute(
                    $product,
                    array_keys($addedAttributes),
                    array_values($addedAttributes)
                );
        
                $variation = ProductVariation::query()->create([
                    'configurable_product_id' => $product->id,
                ]);
        
                new CreatedContentEvent(PRODUCT_VARIATIONS_MODULE_SCREEN_NAME, request(), $variation);
        
                foreach ($addedAttributes as $attribute) {
                    ProductVariationItem::query()->create([
                        'attribute_id' => $attribute,
                        'variation_id' => $variation->id,
                    ]);
                }
        
                $variation = $variation->toArray();
                $variation['variation_default_id'] = $variation['id'];
                $variation['sku'] = $product->sku;
                $variation['auto_generate_sku'] = true;
                $variation['images'] = array_filter((array) $request->input('images', []));
        
                $this->postSaveAllVersions(
                    [$variation['id'] => $variation],
                    $product->id,
                    $this->httpResponse()
                );
            }
        
            // Handle grouped products
            if ($request->has('grouped_products')) {
                GroupedProduct::createGroupedProducts(
                    $product->id,
                    array_map(function ($item) {
                        return [
                            'id' => $item,
                            'qty' => 1,
                        ];
                    }, array_filter(explode(',', $request->input('grouped_products', ''))))
                );
            }

            $this->saveSpecifications($product, $request->specs);
        
            // Return success response
            return $this->httpResponse()
                ->setPreviousUrl(route('products.index'))
                ->setNextUrl(route('products.edit', $product->id))
                ->withUpdatedSuccessMessage();
        }
       
     
    }
    

   
    
    

    public function show(Product $product)
    {
        // Check if the user is an admin
        $isAdmin = auth('web')->check(); // Check if the user is authenticated via the web guard
    
        // Get testimonials for the product
        $testimonials = Review::where('product_id', $product->id)
                              ->when(!$isAdmin, function ($query) {
                                  $query->whereNotNull('star'); // Only include testimonials with stars if not admin
                              })
                              ->get();
    
        // Pass data to the view
        return view('products.show', [
            'product' => $product,
            'testimonials' => $testimonials,
            'isAdmin' => $isAdmin,
        ]);
    }
    
    public function duplicate(Product $product, DuplicateProductService $duplicateProductService)
    {
        $duplicatedProduct = $duplicateProductService->handle($product);

        return $this
            ->httpResponse()
            ->setData([
                'next_url' => route('products.edit', $duplicatedProduct->getKey()),
            ])
            ->setMessage(trans('plugins/ecommerce::ecommerce.forms.duplicate_success_message'));
    }

    public function getProductVariations(Product $product, ProductVariationTable $dataTable)
    {
        $dataTable->setProductId($product->getKey());

        if (EcommerceHelper::isEnabledSupportDigitalProducts() && $product->isTypeDigital()) {
            $dataTable->isDigitalProduct();
        }

        return $dataTable->renderTable();
    }

    public function setDefaultProductVariation(ProductVariation $productVariation)
    {
        ProductVariation::query()
            ->where('configurable_product_id', $productVariation->configurable_product_id)
            ->update(['is_default' => 0]);

        $productVariation->is_default = true;
        $productVariation->save();

        return $this
            ->httpResponse()
            ->withUpdatedSuccessMessage();
    }

    protected function saveSpecifications(Product $product, $specs)
    {
        // Clear existing specs
        $product->specifications()->delete();
    
        // Add new specifications
        if ($specs && is_array($specs)) {
            foreach ($specs as $spec) {
                if (!empty($spec['name']) && !empty($spec['value'])) {
                    Specification::create([
                        'product_id' => $product->id,
                        'spec_name' => $spec['name'],
                        'spec_value' => $spec['value'],
                    ]);
                }
            }
        }
    }   
    
    // public function searchBySku(Request $request)
    // {
    //     $term = $request->get('term');
        
    //     // Debugging: Log the search term
    //     \Log::info('Searching for SKU: ' . $term);
    
    //     // Search SKUs from the ec_products table
    //     $products = Product::where('sku', 'LIKE', "%{$term}%")
    //                        ->get(['id', 'sku']);
    
    //     // Debugging: Log the number of products found
    //     \Log::info('Number of products found: ' . $products->count());
    
    //     // Format the results as an array to be used in the autocomplete
    //     $results = $products->map(function ($product) {
    //         return ['id' => $product->id, 'text' => $product->sku];
    //     });
    
    //     return response()->json($results);
    // }
    // public function searchBySku(Request $request)
    // {
    //     \Log::info('SKU Search Request:', $request->all()); // Log request data
    
    //     $term = $request->get('term');
    
    //     // Search SKUs from the ec_products table
    //     $products = Product::where('sku', 'LIKE', "%{$term}%")->get(['id', 'sku']);
    
    //     \Log::info('Search Results:', $products->toArray()); // Log search results
    
    //     // Format the results as an array to be used in the autocomplete
    //     $results = $products->map(function ($product) {
    //         return ['id' => $product->id, 'sku' => trim($product->sku, "'")];
    //     });
    
    //     return response()->json($results);
    // }
    
    public function searchBySku(Request $request)
    {
        \Log::info('SKU Search Request:', $request->all()); // Log request data
    
        $term = $request->get('term');
    
        // Search SKUs from the ec_products table excluding empty SKUs
        $products = Product::where('sku', 'LIKE', "%{$term}%")
            ->where('sku', '!=', '')
            ->get(['id', 'sku']);
    
        \Log::info('Search Results:', $products->toArray()); // Log search results
    
        // Format the results as an array to be used in the autocomplete
        $results = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'text' => trim($product->sku, "'") // Trim any leading or trailing single quotes
            ];
        });
    
        return response()->json($results);
    }
    
}