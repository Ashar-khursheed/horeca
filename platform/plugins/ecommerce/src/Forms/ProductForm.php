<?php

namespace Botble\Ecommerce\Forms;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\Html;
use Botble\Base\Forms\FieldOptions\ContentFieldOption;
use Botble\Base\Forms\FieldOptions\EditorFieldOption;
use Botble\Base\Forms\FieldOptions\MediaImageFieldOption;
use Botble\Base\Forms\FieldOptions\NameFieldOption;
use Botble\Base\Forms\FieldOptions\NumberFieldOption;
use Botble\Base\Forms\FieldOptions\OnOffFieldOption;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\FieldOptions\StatusFieldOption;
use Botble\Base\Forms\Fields\EditorField;
use Botble\Base\Forms\Fields\MediaImageField;
use Botble\Base\Forms\Fields\MediaImagesField;
use Botble\Base\Forms\Fields\MultiCheckListField;
use Botble\Base\Forms\Fields\NumberField;
use Botble\Base\Forms\Fields\OnOffField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TagField;

use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\Fields\TreeCategoryField;
use Botble\Base\Forms\FormAbstract;
use Botble\Ecommerce\Enums\GlobalOptionEnum;
use Botble\Ecommerce\Enums\ProductTypeEnum;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Facades\ProductCategoryHelper;
use Botble\Ecommerce\Forms\Fronts\Auth\FieldOptions\TextFieldOption;
use Botble\Ecommerce\Http\Requests\ProductRequest;
use Botble\Ecommerce\Models\Brand;
use Botble\Ecommerce\Models\GlobalOption;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\UnitOfMeasurement;
use Botble\Ecommerce\Models\ProductAttributeSet;
use Botble\Ecommerce\Models\ProductCollection;
use Botble\Ecommerce\Models\ProductLabel;
use Botble\Ecommerce\Models\ProductVariation;
use Botble\Ecommerce\Models\Tax;
use Botble\Ecommerce\Tables\ProductVariationTable;
use Illuminate\Support\HtmlString;
use Botble\Ecommerce\Models\Review; // Correct import for the Review model
use Botble\Base\Forms\Fields\FileFieldOption;
use Botble\Base\Forms\Fields\VideoFileField;
use Botble\Ecommerce\Models\TempProduct; // Import your TempProduct model
//use Botble\Ecommerce\Forms\Fields\VideoFileField; // Make sure the path is correct



class ProductForm extends FormAbstract
{
    public function setup(): void
    {
        $this->addAssets();

        $user = Auth::user(); // Get the logged-in user

        // Check if the user's role ID is 18
        $hasContentWritingRole = DB::table('role_users')
            ->where('user_id', $user->id)
            ->where('role_id', 18)
            ->exists();

         // Check if the user's role ID is 19
        $hasGraphicsRole = DB::table('role_users')
        ->where('user_id', $user->id)
        ->where('role_id', 19)
        ->exists();   
        $productspec = DB::table('role_users')
        ->where('user_id', $user->id)
        ->where('role_id', 6)
        ->exists(); 

        $ecomerceRole = DB::table('role_users')
        ->where('user_id', $user->id)
        ->where('role_id', 10)
        ->exists(); 
        if ($hasContentWritingRole) {
              // Additional JavaScript code will be required to disable the field

              $brands = Brand::query()->pluck('name', 'id')->all();

              $productCollections = ProductCollection::query()->pluck('name', 'id')->all();

              $productLabels = ProductLabel::query()->pluck('name', 'id')->all();

              $productId = null;
              $selectedCategories = [];
              $tags = null;
              $producttypes = null;
              $frequently_bought_together= null;
              $totalProductVariations = 0;

          if ($this->getModel()) {
              $productId = $this->getModel()->id;

              $selectedCategories = $this->getModel()->categories()->pluck('category_id')->all();

              $totalProductVariations = ProductVariation::query()->where('configurable_product_id', $productId)->count();

              $tags = $this->getModel()->tags()->pluck('name')->implode(',');
              $producttypes = $this->getModel()->types()->pluck('name')->implode(',');
          }

              $this
              ->add('name', TextField::class, NameFieldOption::make()->required()->toArray())

              ->add(
                  'description',
                  EditorField::class,
                  EditorFieldOption::make()
                      ->label(trans('core/base::forms.description'))
                      ->placeholder(trans('core/base::forms.description_placeholder'))->toArray()
              )
            
              ->add('content', EditorField::class, ContentFieldOption::make() ->label(trans('Features'))->allowedShortcodes()->toArray());
           
            
         


            // ->addMetaBoxes([
            //     'add_specs' => [
            //         'title' => 'Add Specs',
            //         'content' => view('plugins/ecommerce::products.partials.add-specs-form'),
            //         'priority' => 5,
            //         'before_wrapper' => '<div id="specs-wrapper" style="display: flex; flex-wrap: wrap; gap: 10px;">',
            //         'after_wrapper' => '</div>',
            //     ],
            // ])

            
    
    
                $this
                    
                    ->addAfter('brand_id', 'sku', TextField::class, TextFieldOption::make()->label(trans('plugins/ecommerce::products.sku')));
         }
       
            
        
        else if ($hasGraphicsRole) {

              $this
              ->add('name', TextField::class, NameFieldOption::make()->required()->toArray())
            ->add('images[]', MediaImagesField::class, [
                'label' => trans('plugins/ecommerce::products.form.image'),
                'values' => $this->getModel() ? $this->getModel()->images : [],
                'attributes' => [
                    'id' => 'media-images-field',
                    'class' => 'form-control media-upload-field', // Add a class for JavaScript targeting
                ]
            ])
            ->add('sku', TextField::class, TextFieldOption::make()->label(trans('plugins/ecommerce::products.sku')))
             ->addMetaBoxes([
                    'Video' => [
                        'title' => 'Product Videos',
                        'content' => view('plugins/ecommerce::products.partials.video-upload', [
                            // Decode the video paths JSON before passing to the view
                            'videos' => !empty($this->getModel()->video_path) ? json_decode($this->getModel()->video_path, true) : [],
                        ]),
                        'priority' => 50,
                    ],
                ]);
            // ->add('video', 'file', [
            //     'label' => trans('Upload Video'), // Label for the field
            //     'accept' => 'video/*', // Accept video files only
            //     'maxFileSize' => 5000, // Max file size in kilobytes (5MB)
            // ]);
                // Script to open the media upload modal when the user clicks on the field
          
        }
        else if ( $ecomerceRole)
        {
                    
            $brands = Brand::query()->pluck('name', 'id')->all();

            $productCollections = ProductCollection::query()->pluck('name', 'id')->all();

            $productLabels = ProductLabel::query()->pluck('name', 'id')->all();

            $productId = null;
            $selectedCategories = [];
            $tags = null;
            $producttypes = null;
            $frequently_bought_together= null;
            $totalProductVariations = 0;

            if ($this->getModel()) {
                $productId = $this->getModel()->id;

                $selectedCategories = $this->getModel()->categories()->pluck('category_id')->all();

                $totalProductVariations = ProductVariation::query()->where('configurable_product_id', $productId)->count();

                $tags = $this->getModel()->tags()->pluck('name')->implode(',');
                $producttypes = $this->getModel()->types()->pluck('name')->implode(',');
            }

            $this
            ->setupModel(new Product())
            ->setValidatorClass(ProductRequest::class)
            ->setFormOption('files', true)
            ->add('name', TextField::class, NameFieldOption::make()->required()->toArray())
            ->add('sku', TextField::class, TextFieldOption::make()->label(trans('plugins/ecommerce::products.sku')))

           
            // ->add(
            //     'description',
            //     EditorField::class,
            //     EditorFieldOption::make()
            //         ->label(trans('core/base::forms.description'))
            //         ->placeholder(trans('core/base::forms.description_placeholder'))->toArray()
            // )
            // ->add('content', EditorField::class, ContentFieldOption::make()->allowedShortcodes()->toArray())
            // ->add('images[]', MediaImagesField::class, [
            //     'label' => trans('plugins/ecommerce::products.form.image'),
            //     'values' => $productId ? $this->getModel()->images : [],
            // ])

        
            
            // ->add('video', 'file', [
            //     'label' => trans('Upload Video'), // Label for the field
            //     'accept' => 'video/*', // Accept video files only
            //     'maxFileSize' => 51200, // Max file size in kilobytes (50MB)
            // ])
            

        

            // ->addMetaBoxes([
            //     'specs' => [
            //         'title' => 'Add Specs',
            //         'content' => new HtmlString(
            //             Html::tag('div', 
            //                 Html::tag('input', '', [
            //                     'name' => 'specs_sheet_heading',
            //                     'placeholder' => 'Specs Heading',
            //                     'class' => 'form-control'
            //                 ]) .
            //                 Html::tag('div', '', [
            //                     'class' => 'specs-items',
            //                     'data-repeater-list' => 'specs_sheet',
            //                 ]), 
            //                 ['class' => 'specs-container']
            //             )
            //         ),
            //         'wrap' => false,
            //         'priority' => 1,
            //     ],
            // ])

            // ->addMetaBoxes([
            //     'specs' => [
            //         'title' => 'Specifications',
            //         'content' => view('plugins/ecommerce::products.partials.specs-form', [
            //             'specs' => $this->getModel()->specifications ?? [], // Fetch existing specs if editing
            //         ]),
            //         'priority' => 50,
            //     ],
            // ])

            ->addMetaBoxes([
                            'documents' => [
                    'title' => 'Product Documents',
                    'content' => view('plugins/ecommerce::products.partials.documents-form', [
                        'documents' => $this->getModel()->documents ?? [], // Fetch existing documents if editing
                    ]),
                    'priority' => 60,
                ],
            ])


                            // Add this to the meta boxes array in the ProductForm class
            // Remove this block
            //   ->addMetaBoxes([
            //     'temp_product_changes' => [
            //         'title' => 'Review Product Changes',
            //         'content' => view('plugins/ecommerce::products.partials.product-changes-button', [
            //             // You can pass additional data here if needed
            //         ])->render(),
            //         'wrap' => false,
            //         'priority' => 100,
            //     ],
            // ])
            


            
            ->add('product_type', 'hidden', [
                'value' => request()->input('product_type') ?: ProductTypeEnum::PHYSICAL,
            ])
            ->add('status', SelectField::class, StatusFieldOption::make()->toArray())
            ->add(
                'is_featured',
                OnOffField::class,
                OnOffFieldOption::make()
                    ->label(trans('core/base::forms.is_featured'))
                    ->defaultValue(false)
                    ->toArray()
            )
        
            ->add(
                'categories[]',
                TreeCategoryField::class,
                SelectFieldOption::make()
                    ->label(trans('plugins/ecommerce::products.form.categories'))
                    ->choices(ProductCategoryHelper::getActiveTreeCategories())
                    ->selected(old('categories', $selectedCategories))
                    ->addAttribute('card-body-class', 'p-0')
                    ->toArray()
            )
            ->when($brands, function () use ($brands) {
                $this
                    ->add(
                        'brand_id',
                        SelectField::class,
                        SelectFieldOption::make()
                            ->label(trans('plugins/ecommerce::products.form.brand'))
                            ->choices($brands)
                            ->searchable()
                            ->emptyValue(trans('plugins/ecommerce::brands.select_brand'))
                            ->allowClear()
                            ->toArray()
                    );
            })
            ->add(
                'image',
                MediaImageField::class,
                MediaImageFieldOption::make()
                    ->label(trans('plugins/ecommerce::products.form.featured_image'))
                    ->toArray()
            )
            ->when($productCollections, function () use ($productCollections) {
                $selectedProductCollections = [];

                if ($this->getModel() && $this->getModel()->getKey()) {
                    $selectedProductCollections = $this->getModel()
                        ->productCollections()
                        ->pluck('product_collection_id')
                        ->all();
                }

                $this
                    ->add('product_collections[]', MultiCheckListField::class, [
                    'label' => trans('plugins/ecommerce::products.form.collections'),
                    'choices' => $productCollections,
                    'value' => old('product_collections', $selectedProductCollections),
                ]);
            })
            ->when($productLabels, function () use ($productLabels) {
                $selectedProductLabels = [];

                if ($this->getModel() && $this->getModel()->getKey()) {
                    $selectedProductLabels = $this->getModel()->productLabels()->pluck('product_label_id')->all();
                }

                $this
                    ->add('product_labels[]', MultiCheckListField::class, [
                        'label' => trans('plugins/ecommerce::products.form.labels'),
                        'choices' => $productLabels,
                        'value' => old('product_labels', $selectedProductLabels),
                    ]);
            })
            ->when(EcommerceHelper::isTaxEnabled(), function () {
                $taxes = Tax::query()->orderBy('percentage')->get()->pluck('title_with_percentage', 'id')->all();

                if ($taxes) {
                    $selectedTaxes = [];
                    if ($this->getModel() && $this->getModel()->getKey()) {
                        $selectedTaxes = $this->getModel()->taxes()->pluck('tax_id')->all();
                    } elseif ($defaultTaxRate = get_ecommerce_setting('default_tax_rate')) {
                        $selectedTaxes = [$defaultTaxRate];
                    }

                    $this->add('taxes[]', MultiCheckListField::class, [
                        'label' => trans('plugins/ecommerce::products.form.taxes'),
                        'choices' => $taxes,
                        'value' => old('taxes', $selectedTaxes),
                    ]);
                }
            })
            ->when(EcommerceHelper::isCartEnabled(), function (ProductForm $form) {
                $form
                    ->add(
                        'minimum_order_quantity',
                        NumberField::class,
                        NumberFieldOption::make()
                            ->label(trans('plugins/ecommerce::products.form.minimum_order_quantity'))
                            ->helperText(trans('plugins/ecommerce::products.form.minimum_order_quantity_helper'))
                            ->defaultValue(0)
                            ->toArray()
                    )
                    ->add(
                        'maximum_order_quantity',
                        NumberField::class,
                        NumberFieldOption::make()
                            ->label(trans('plugins/ecommerce::products.form.maximum_order_quantity'))
                            ->helperText(trans('plugins/ecommerce::products.form.maximum_order_quantity_helper'))
                            ->defaultValue(0)
                            ->toArray()
                    );
            })
            ->add('tag', TagField::class, [
                'label' => trans('plugins/ecommerce::products.form.tags'),
                'value' => $tags,
                'attr' => [
                    'placeholder' => trans('plugins/ecommerce::products.form.write_some_tags'),
                    'data-url' => route('product-tag.all'),
                ],
            ])

            ->add('producttypes', TagField::class, [
                'label' => trans('plugins/ecommerce::products.form.producttypes'),
                'value' => $producttypes,
                'attr' => [
                    'placeholder' => trans('plugins/ecommerce::products.form.write_some_producttypes'),
                    'data-url' => route('product-types.all'),
                ],
            ])

            // ->add('frequently_bought_together', TagField::class, [
            //     'label' => trans('plugins/ecommerce::products.form.frequently_bought_together'),
            //     'attr' => [
            //         'placeholder' => trans('plugins/ecommerce::products.form.search_sku'),
            //         'data-url' => route('products.search-sku'), // ensure this route exists
            //     ],
            //     'value' => $frequently_bought_together, // fetch the value from the request or model
            // ])
            // Assuming you have set this variable in your controller as shown above
                    // ->add('frequently_bought_together', TagField::class, [
                    //     'label' => trans('plugins/ecommerce::products.form.frequently_bought_together'),
                    //     'attr' => [
                    //         'placeholder' => trans('plugins/ecommerce::products.form.search_sku'),
                    //         'class' => 'form-control',
                    //         'data-url' => route('products.search-sku'), // AJAX URL for SKU search
                    //     ],
                    //     'value' => $frequently_bought_together, // Pass existing values to the field
                    // ])

                    ->add('frequently_bought_together', TagField::class, [
                        'label' => trans('plugins/ecommerce::products.form.frequently_bought_together'),
                        'attr' => [
                            'placeholder' => trans('plugins/ecommerce::products.form.search_sku'),
                            'class' => 'form-control',
                            'data-url' => route('products.search-sku'), // AJAX URL for SKU search
                        ],
                        'value' => $frequently_bought_together, // Pass existing values to the field
                    ])
                    
            // ->addMetaBoxes([
            //     'admin_reviews' => [
            //         'title' => 'Admin Reviews',
            //         'content' => view('plugins/ecommerce::products.partials.admin-reviews', [
            //             'reviews' => Review::where('product_id', $this->getModel()->id)
            //                               ->whereNotNull('star')
            //                               ->when(auth('web')->check() && auth('web')->user()->is_admin, function($query) {
            //                                   // Adjust the condition based on how you identify admin users
            //                                   $query->whereHas('user', function($query) {
            //                                       $query->whereIn('id', User::where('is_admin', true)->pluck('id'));
            //                                   });
            //                               })
            //                               ->get()
            //         ])->render(), // Ensure the content is a string
            //         'wrap' => false,
            //         'priority' => 6,
            //     ],
            //     // 'add_testimonials' => [
            //     //     'title' => 'Add Testimonials',
            //     //     'content' => view('plugins/ecommerce::products.partials.Testimonials-form', [
            //     //         'testimonials' => $this->getModel()->reviews ?? []
            //     //     ])->render(), // Ensure the content is a string
            //     //     'wrap' => false,
            //     //     'priority' => 7,
            //     // ],
            // ])
            
            


                //->add('handle', 'text', ['label' => 'Handle'])
            // ->add('variant_grams', 'text', ['label' => 'Variant Grams'])
            // ->add('variant_inventory_tracker', 'text', ['label' => 'Variant Inventory Tracker'])
            // ->add('variant_inventory_quantity', 'number', ['label' => 'Variant Inventory Quantity'])
            // ->add('variant_inventory_policy', 'text', ['label' => 'Variant Inventory Policy'])
            // ->add('variant_fulfillment_service', 'text', ['label' => 'Variant Fulfillment Service'])
            // ->add('variant_requires_shipping', 'text', ['label' => 'Variant Grams'])
            // ->add('variant_barcode', 'text', ['label' => 'Variant Barcode'])
            // ->add('gift_card', 'text', ['label' => 'Variant Grams'])
            // ->add('seo_title', 'text', ['label' => 'SEO Title'])
            // ->add('seo_description', 'textarea', ['label' => 'SEO Description'])
            ->add('google_shopping_category', 'text', ['label' => 'Google Shopping / Google Product Category'])
            // ->add('google_shopping_gender', 'text', ['label' => 'Google Shopping / Gender'])
            // ->add('google_shopping_age_group', 'text', ['label' => 'Google Shopping / Age Group'])
            // ->add('google_shopping_mpn', 'text', ['label' => 'Google Shopping / MPN'])
            // ->add('google_shopping_condition', 'text', ['label' => 'Google Shopping / Condition'])
            // ->add('google_shopping_custom_product', 'text', ['label' => 'Google Shopping / Custom Product'])
            // ->add('google_shopping_custom_label_0', 'text', ['label' => 'Google Shopping / Custom Label 0'])
            // ->add('google_shopping_custom_label_1', 'text', ['label' => 'Google Shopping / Custom Label 1'])
            // ->add('google_shopping_custom_label_2', 'text', ['label' => 'Google Shopping / Custom Label 2'])
            // ->add('google_shopping_custom_label_3', 'text', ['label' => 'Google Shopping / Custom Label 3'])
            // ->add('google_shopping_custom_label_4', 'text', ['label' => 'Google Shopping / Custom Label 4'])
            ->add('box_quantity', 'number', ['label' => 'Box Quantity'])
            // ->add('technical_table', 'text', ['label' => 'Technical Table'])
            // ->add('technical_spec', 'text', ['label' => 'Technical Spec'])
            ->add('product_label', 'text', ['label' => 'Product Label'])

            // ->addMetaBoxes([
            //     'add_specs' => [
            //         'title' => 'Add Specs',
            //         'content' => view('plugins/ecommerce::products.partials.add-specs-form'),
            //         'priority' => 5,
            //         'before_wrapper' => '<div id="specs-wrapper" style="display: flex; flex-wrap: wrap; gap: 10px;">',
            //         'after_wrapper' => '</div>',
            //     ],
            // ])


            ->setBreakFieldPoint('status');

                    if (EcommerceHelper::isEnabledProductOptions()) {
                        $this
                            ->addMetaBoxes([
                                'product_options_box' => [
                                    'title' => trans('plugins/ecommerce::product-option.name'),
                                    'content' => view('plugins/ecommerce::products.partials.product-option-form', [
                                        'options' => GlobalOptionEnum::options(),
                                        'globalOptions' => GlobalOption::query()->pluck('name', 'id')->all(),
                                        'product' => $this->getModel(),
                                        'routes' => [
                                            'ajax_option_info' => route('global-option.ajaxInfo'),
                                        ],
                                    ]),
                                    'priority' => 4,
                                ],
                            ]);
                    }

                $productAttributeSets = ProductAttributeSet::getAllWithSelected($productId, []);

                $this
                    ->addMetaBoxes([
                        'attribute-sets' => [
                            'content' => '',
                            'before_wrapper' => '<div class="d-none product-attribute-sets-url" data-url="' . route('products.product-attribute-sets') . '">',
                            'after_wrapper' => '</div>',
                            'priority' => 3,
                        ],
                    ]);

                    if (! $totalProductVariations) {
                        $this
                    ->removeMetaBox('variations')
                    ->addMetaBoxes([
                   
                    'attributes' => [
                        'before_wrapper' => '<div id="main-manage-product-type">',
                        'title' => trans('plugins/ecommerce::products.attributes'),
                        'content' => view('plugins/ecommerce::products.partials.add-product-attributes', [
                            'product' => $this->getModel(),
                            'productAttributeSets' => $productAttributeSets,
                            'addAttributeToProductUrl' => $this->getModel()->id
                                ? route('products.add-attribute-to-product', $this->getModel()->id)
                                : null,
                        ]),
                        'header_actions' => $productAttributeSets->isNotEmpty()
                            ? view('plugins/ecommerce::products.partials.product-attribute-actions')
                            : null,
                        'after_wrapper' => '</div>',
                        'priority' => 3,
                    ],
                    ]);
                    } elseif ($productId) {
                    $productVariationTable = app(ProductVariationTable::class)
                        ->setProductId($productId)
                        ->setProductAttributeSets($productAttributeSets);

                    if (EcommerceHelper::isEnabledSupportDigitalProducts() && $this->getModel()->isTypeDigital()) {
                        $productVariationTable->isDigitalProduct();
                    }

                    $this
                        ->removeMetaBox('general')
                     ->addMetaBoxes([
                    'variations' => [
                        'title' => trans('plugins/ecommerce::products.product_has_variations'),
                        'content' => view('plugins/ecommerce::products.partials.configurable', [
                            'product' => $this->getModel(),
                            'productAttributeSets' => $productAttributeSets,
                            'productVariationTable' => $productVariationTable,
                        ]),
                        'header_actions' => view(
                            'plugins/ecommerce::products.partials.product-variation-actions',
                            ['product' => $this->getModel()]
                        ),
                        'has_table' => true,
                        'before_wrapper' => '<div id="main-manage-product-type">',
                        'after_wrapper' => '</div>',
                        'priority' => 3,
                        'render' => false,
                    ],
                 ])
                 ->addAfter('brand_id', 'sku', TextField::class, TextFieldOption::make()->label(trans('plugins/ecommerce::products.sku')));
                }

                if ($productId && is_in_admin(true)) {
                    add_filter('base_action_form_actions_extra', function () {
                        return view('plugins/ecommerce::forms.duplicate-action', ['product' => $this->getModel()])->render();
                    });
                }
        }
        elseif ( $productspec)
        {
                
                        $brands = Brand::query()->pluck('name', 'id')->all();

                        $productCollections = ProductCollection::query()->pluck('name', 'id')->all();

                        $productLabels = ProductLabel::query()->pluck('name', 'id')->all();

                        $productId = null;
                        $selectedCategories = [];
                        $tags = null;
                        $producttypes = null;
                        $frequently_bought_together= null;
                        
                        $totalProductVariations = 0;

                    if ($this->getModel()) {
                        $productId = $this->getModel()->id;

                        $selectedCategories = $this->getModel()->categories()->pluck('category_id')->all();

                        $totalProductVariations = ProductVariation::query()->where('configurable_product_id', $productId)->count();

                        $tags = $this->getModel()->tags()->pluck('name')->implode(',');
                        $producttypes = $this->getModel()->types()->pluck('name')->implode(',');
                    }

                    $this
                    ->setupModel(new Product())
                    ->setValidatorClass(ProductRequest::class)
                    ->setFormOption('files', true)
                    ->add('name', TextField::class, NameFieldOption::make()->required()->toArray())
                           
                               ->add('sku', 'text', ['label' => 'SKU'])
                
                    ->add(
                        'warranty_information',
                        EditorField::class,
                        EditorFieldOption::make()
                            ->label(trans('warranty information'))
                            ->placeholder(trans('core/base::forms.description_placeholder'))->toArray()
                    )
                      ->addMetaBoxes([
                        'shipping_weight' => [
                            'title' => 'Shipping Weight',
                            'content' => view('plugins/ecommerce::products.partials.shipping-weight-form', [
                                'shipping_weight' => $this->getModel()->shipping_weight ?? null, // Fetch existing shipping weight if editing
                                'shipping_weight_option' => $this->getModel()->shipping_weight_option ?? null, // Fetch existing shipping weight option
                            ]),
                            'priority' => 50,
                        ],
                    ])
                    // ->addMetaBoxes([
                       
                    //     'comparisons' => [
                    //         'title' => 'Comparison Products',
                    //         'content' => view('plugins/ecommerce::products.partials.comparison_form', [
                    //             'comparisons' => $this->getModel()->comparisons ?? [], // Fetch existing comparisons if editing
                    //             'products' => Product::pluck('sku', 'id')->toArray(), // Fetch SKUs for the dropdown
                    //         ]),
                    //         'priority' => 51,
                    //     ],
                    // ])
                    
                    ->addMetaBoxes([
                        'comparisons' => [
                            'title' => 'Comparison Products',
                            'content' => view('plugins/ecommerce::products.partials.comparison_form', [
                                'comparisons' => $comparisons,
                                'products' => Product::pluck('sku', 'id')->toArray(),
                            ]),
                            'priority' => 51,
                        ],
                    ])
                    
                    
            
                    ->addMetaBoxes([
                        'specs' => [
                            'title' => 'Specifications',
                            'content' => view('plugins/ecommerce::products.partials.specs-form', [
                                'specs' => $this->getModel()->specifications ?? [], // Fetch existing specs if editing
                            ]),
                            'priority' => 50,
                        ],
                    ])



                    
                    ->add('product_type', 'hidden', [
                        'value' => request()->input('product_type') ?: ProductTypeEnum::PHYSICAL,
                    ])
                    ->add('status', SelectField::class, StatusFieldOption::make()->toArray())
                    ->add(
                        'is_featured',
                        OnOffField::class,
                        OnOffFieldOption::make()
                            ->label(trans('core/base::forms.is_featured'))
                            ->defaultValue(false)
                            ->toArray()
                    )
                
                    ->add(
                        'categories[]',
                        TreeCategoryField::class,
                        SelectFieldOption::make()
                            ->label(trans('plugins/ecommerce::products.form.categories'))
                            ->choices(ProductCategoryHelper::getActiveTreeCategories())
                            ->selected(old('categories', $selectedCategories))
                            ->addAttribute('card-body-class', 'p-0')
                            ->toArray()
                    )
                    ->when($brands, function () use ($brands) {
                        $this
                            ->add(
                                'brand_id',
                                SelectField::class,
                                SelectFieldOption::make()
                                    ->label(trans('plugins/ecommerce::products.form.brand'))
                                    ->choices($brands)
                                    ->searchable()
                                    ->emptyValue(trans('plugins/ecommerce::brands.select_brand'))
                                    ->allowClear()
                                    ->toArray()
                            );
                    })
                
                    ->when($productCollections, function () use ($productCollections) {
                        $selectedProductCollections = [];

                        if ($this->getModel() && $this->getModel()->getKey()) {
                            $selectedProductCollections = $this->getModel()
                                ->productCollections()
                                ->pluck('product_collection_id')
                                ->all();
                        }

                        $this
                            ->add('product_collections[]', MultiCheckListField::class, [
                            'label' => trans('plugins/ecommerce::products.form.collections'),
                            'choices' => $productCollections,
                            'value' => old('product_collections', $selectedProductCollections),
                        ]);
                    })
                    ->when($productLabels, function () use ($productLabels) {
                        $selectedProductLabels = [];

                        if ($this->getModel() && $this->getModel()->getKey()) {
                            $selectedProductLabels = $this->getModel()->productLabels()->pluck('product_label_id')->all();
                        }

                        $this
                            ->add('product_labels[]', MultiCheckListField::class, [
                                'label' => trans('plugins/ecommerce::products.form.labels'),
                                'choices' => $productLabels,
                                'value' => old('product_labels', $selectedProductLabels),
                            ]);
                    })
                    // ->when(EcommerceHelper::isTaxEnabled(), function () {
                    //     $taxes = Tax::query()->orderBy('percentage')->get()->pluck('title_with_percentage', 'id')->all();

                    //     if ($taxes) {
                    //         $selectedTaxes = [];
                    //         if ($this->getModel() && $this->getModel()->getKey()) {
                    //             $selectedTaxes = $this->getModel()->taxes()->pluck('tax_id')->all();
                    //         } elseif ($defaultTaxRate = get_ecommerce_setting('default_tax_rate')) {
                    //             $selectedTaxes = [$defaultTaxRate];
                    //         }

                    //         $this->add('taxes[]', MultiCheckListField::class, [
                    //             'label' => trans('plugins/ecommerce::products.form.taxes'),
                    //             'choices' => $taxes,
                    //             'value' => old('taxes', $selectedTaxes),
                    //         ]);
                    //     }
                    // })
                    // ->when(EcommerceHelper::isCartEnabled(), function (ProductForm $form) {
                    //     $form
                    //         ->add(
                    //             'minimum_order_quantity',
                    //             NumberField::class,
                    //             NumberFieldOption::make()
                    //                 ->label(trans('plugins/ecommerce::products.form.minimum_order_quantity'))
                    //                 ->helperText(trans('plugins/ecommerce::products.form.minimum_order_quantity_helper'))
                    //                 ->defaultValue(0)
                    //                 ->toArray()
                    //         )
                    //         ->add(
                    //             'maximum_order_quantity',
                    //             NumberField::class,
                    //             NumberFieldOption::make()
                    //                 ->label(trans('plugins/ecommerce::products.form.maximum_order_quantity'))
                    //                 ->helperText(trans('plugins/ecommerce::products.form.maximum_order_quantity_helper'))
                    //                 ->defaultValue(0)
                    //                 ->toArray()
                    //         );
                    // })
                    ->add('tag', TagField::class, [
                        'label' => trans('plugins/ecommerce::products.form.tags'),
                        'value' => $tags,
                        'attr' => [
                            'placeholder' => trans('plugins/ecommerce::products.form.write_some_tags'),
                            'data-url' => route('product-tag.all'),
                        ],
                    ])
                    ->add('producttypes', TagField::class, [
                        'label' => trans('plugins/ecommerce::products.form.producttypes'),
                        'value' => $producttypes,
                        'attr' => [
                            'placeholder' => trans('plugins/ecommerce::products.form.write_some_producttypes'),
                            'data-url' => route('product-types.all'),
                        ],
                    ])

                    ->add('frequently_bought_together', TagField::class, [
                        'label' => trans('plugins/ecommerce::products.form.frequently_bought_together'),
                        'attr' => [
                            'placeholder' => trans('plugins/ecommerce::products.form.search_sku'),
                            'data-url' => route('products.search-sku'), // ensure this route exists
                        ],
                        'value' => $frequently_bought_together, // fetch the value from the request or model
                    ])
                    
                    // ->addMetaBoxes([
                    //     'admin_reviews' => [
                    //         'title' => 'Admin Reviews',
                    //         'content' => view('plugins/ecommerce::products.partials.admin-reviews', [
                    //             'reviews' => Review::where('product_id', $this->getModel()->id)
                    //                               ->whereNotNull('star')
                    //                               ->when(auth('web')->check() && auth('web')->user()->is_admin, function($query) {
                    //                                   // Adjust the condition based on how you identify admin users
                    //                                   $query->whereHas('user', function($query) {
                    //                                       $query->whereIn('id', User::where('is_admin', true)->pluck('id'));
                    //                                   });
                    //                               })
                    //                               ->get()
                    //         ])->render(), // Ensure the content is a string
                    //         'wrap' => false,
                    //         'priority' => 6,
                    //     ],
                    //     // 'add_testimonials' => [
                    //     //     'title' => 'Add Testimonials',
                    //     //     'content' => view('plugins/ecommerce::products.partials.Testimonials-form', [
                    //     //         'testimonials' => $this->getModel()->reviews ?? []
                    //     //     ])->render(), // Ensure the content is a string
                    //     //     'wrap' => false,
                    //     //     'priority' => 7,
                    //     // ],
                    // ])
                    
                    


                    //->add('handle', 'text', ['label' => 'Handle'])
                // ->add('variant_grams', 'text', ['label' => 'Variant Grams'])
                // ->add('variant_inventory_tracker', 'text', ['label' => 'Variant Inventory Tracker'])
                // ->add('variant_inventory_quantity', 'number', ['label' => 'Variant Inventory Quantity'])
                // ->add('variant_inventory_policy', 'text', ['label' => 'Variant Inventory Policy'])
                // ->add('variant_fulfillment_service', 'text', ['label' => 'Variant Fulfillment Service'])
                // ->add('variant_requires_shipping', 'text', ['label' => 'Variant Grams'])
                // ->add('variant_barcode', 'text', ['label' => 'Variant Barcode'])
                // ->add('gift_card', 'text', ['label' => 'Variant Grams'])
                // ->add('seo_title', 'text', ['label' => 'SEO Title'])
                // ->add('seo_description', 'textarea', ['label' => 'SEO Description'])
                // ->add('variant_requires_shipping', 'select', [
                //     'label' => 'Variant Requires Shipping',
                //     'choices' => [
                //         1 => 'Yes',   // True
                //         0 => 'No'     // False
                //     ],
                  
                //     'attr' => ['class' => 'form-control']
                // ])
                // ->add('Refund Policy', 'select', [
                //     'label' => 'Refund Policy',
                //     'choices' => [
                //         0 => '15 Days',   
                //         1 => '90 Days' ,    
                //         2 => 'Non Refundable'    
                //     ],
                //     'selected' => $model->refund ?? 2,  // Set default selected value if needed
                //     'attr' => ['class' => 'form-control']

                    
                // ])
                // ->add('refund', 'text', ['label' => 'Refund Policy'])
                // ->add('refund', 'select', [
                //     'label' => 'Refund Policy',
                //     'choices' => [
                //         'non-refundable' => 'Non-refundable',
                //         '15 days' => '15 Days Refund',
                //         '90 days' => '90 Days Refund',
                //     ],
                    
                //     'attr' => [
                //         'class' => 'form-control',
                //     ],
                    
                // ])
                
                ->add('google_shopping_category', 'text', ['label' => 'Google Shopping / Google Product Category'])
                
            //  ->add('unit_of_measurement_id', 'select', [
            //         'label' => 'Unit of Measurement',
            //         'choices' => UnitOfMeasurement::pluck('name', 'id')->toArray(), // Fetch the list of units from the DB
            //         'empty_value' => 'Select a Unit' // Optional placeholder
            //     ])
            //     ->add('delivery_date', 'date', ['label' => 'Delivery Date'])

                // ->add('google_shopping_gender', 'text', ['label' => 'Google Shopping / Gender'])
                // ->add('google_shopping_age_group', 'text', ['label' => 'Google Shopping / Age Group'])
                ->add('google_shopping_mpn', 'text', ['label' => 'Google Shopping / MPN'])
                // ->add('google_shopping_condition', 'text', ['label' => 'Google Shopping / Condition'])
                // ->add('google_shopping_custom_product', 'text', ['label' => 'Google Shopping / Custom Product'])
                // ->add('google_shopping_custom_label_0', 'text', ['label' => 'Google Shopping / Custom Label 0'])
                // ->add('google_shopping_custom_label_1', 'text', ['label' => 'Google Shopping / Custom Label 1'])
                // ->add('google_shopping_custom_label_2', 'text', ['label' => 'Google Shopping / Custom Label 2'])
                // ->add('google_shopping_custom_label_3', 'text', ['label' => 'Google Shopping / Custom Label 3'])
                // ->add('google_shopping_custom_label_4', 'text', ['label' => 'Google Shopping / Custom Label 4'])
                // ->add('box_quantity', 'number', ['label' => 'Box Quantity'])
                // ->add('technical_table', 'text', ['label' => 'Technical Table'])
                // ->add('technical_spec', 'text', ['label' => 'Technical Spec'])
                // ->add('product_label', 'text', ['label' => 'Product Label'])


                    // ->addMetaBoxes([
                    //     'add_specs' => [
                    //         'title' => 'Add Specs',
                    //         'content' => view('plugins/ecommerce::products.partials.add-specs-form'),
                    //         'priority' => 5,
                    //         'before_wrapper' => '<div id="specs-wrapper" style="display: flex; flex-wrap: wrap; gap: 10px;">',
                    //         'after_wrapper' => '</div>',
                    //     ],
                    // ])

                 
                    ->setBreakFieldPoint('status');

                if (EcommerceHelper::isEnabledProductOptions()) {
                    $this
                        ->addMetaBoxes([
                            'product_options_box' => [
                                'title' => trans('plugins/ecommerce::product-option.name'),
                                'content' => view('plugins/ecommerce::products.partials.product-option-form', [
                                    'options' => GlobalOptionEnum::options(),
                                    'globalOptions' => GlobalOption::query()->pluck('name', 'id')->all(),
                                    'product' => $this->getModel(),
                                    'routes' => [
                                        'ajax_option_info' => route('global-option.ajaxInfo'),
                                    ],
                                ]),
                                'priority' => 4,
                            ],
                        ]);
                }

                $productAttributeSets = ProductAttributeSet::getAllWithSelected($productId, []);

                $this
                    ->addMetaBoxes([
                        'attribute-sets' => [
                            'content' => '',
                            'before_wrapper' => '<div class="d-none product-attribute-sets-url" data-url="' . route('products.product-attribute-sets') . '">',
                            'after_wrapper' => '</div>',
                            'priority' => 3,
                        ],
                    ]);

                if (! $totalProductVariations) {
                    $this
                        ->removeMetaBox('variations')
                        ->addMetaBoxes([
                            'general' => [
                                'title' => trans('plugins/ecommerce::products.overview'),
                                'content' => view(
                                    'plugins/ecommerce::products.partials.general',
                                    [
                                        'product' => $productId ? $this->getModel() : null,
                                        'isVariation' => false,
                                        'originalProduct' => null,
                                    ]
                                ),
                                'before_wrapper' => '<div id="main-manage-product-type">',
                                'priority' => 2,
                            ],
                            'attributes' => [
                                'title' => trans('plugins/ecommerce::products.attributes'),
                                'content' => view('plugins/ecommerce::products.partials.add-product-attributes', [
                                    'product' => $this->getModel(),
                                    'productAttributeSets' => $productAttributeSets,
                                    'addAttributeToProductUrl' => $this->getModel()->id
                                        ? route('products.add-attribute-to-product', $this->getModel()->id)
                                        : null,
                                ]),
                                'header_actions' => $productAttributeSets->isNotEmpty()
                                    ? view('plugins/ecommerce::products.partials.product-attribute-actions')
                                    : null,
                                'after_wrapper' => '</div>',
                                'priority' => 3,
                            ],
                        ]);
                } elseif ($productId) {
                    $productVariationTable = app(ProductVariationTable::class)
                        ->setProductId($productId)
                        ->setProductAttributeSets($productAttributeSets);

                    if (EcommerceHelper::isEnabledSupportDigitalProducts() && $this->getModel()->isTypeDigital()) {
                        $productVariationTable->isDigitalProduct();
                    }

                    $this
                        ->removeMetaBox('general')
                        ->addMetaBoxes([
                            'variations' => [
                                'title' => trans('plugins/ecommerce::products.product_has_variations'),
                                'content' => view('plugins/ecommerce::products.partials.configurable', [
                                    'product' => $this->getModel(),
                                    'productAttributeSets' => $productAttributeSets,
                                    'productVariationTable' => $productVariationTable,
                                ]),
                                'header_actions' => view(
                                    'plugins/ecommerce::products.partials.product-variation-actions',
                                    ['product' => $this->getModel()]
                                ),
                                'has_table' => true,
                                'before_wrapper' => '<div id="main-manage-product-type">',
                                'after_wrapper' => '</div>',
                                'priority' => 3,
                                'render' => false,
                            ],
                        ])
                        ->addAfter('brand_id', 'sku', TextField::class, TextFieldOption::make()->label(trans('plugins/ecommerce::products.sku')));
                }

                if ($productId && is_in_admin(true)) {
                    add_filter('base_action_form_actions_extra', function () {
                        return view('plugins/ecommerce::forms.duplicate-action', ['product' => $this->getModel()])->render();
                    });
                }
    }
        
          else
            {
                    
                            $brands = Brand::query()->pluck('name', 'id')->all();

                            $productCollections = ProductCollection::query()->pluck('name', 'id')->all();

                            $productLabels = ProductLabel::query()->pluck('name', 'id')->all();

                            $productId = null;
                            $selectedCategories = [];
                            $tags = null;
                            $producttypes = null;
                            $frequently_bought_together= null;
                            
                            $totalProductVariations = 0;

                        if ($this->getModel()) {
                            $productId = $this->getModel()->id;

                            $selectedCategories = $this->getModel()->categories()->pluck('category_id')->all();

                            $totalProductVariations = ProductVariation::query()->where('configurable_product_id', $productId)->count();

                            $tags = $this->getModel()->tags()->pluck('name')->implode(',');
                            $producttypes = $this->getModel()->producttypes()->pluck('name')->implode(',');
                        }

                        $this
                        ->setupModel(new Product())
                        ->setValidatorClass(ProductRequest::class)
                        ->setFormOption('files', true)
                        ->add('name', TextField::class, NameFieldOption::make()->required()->toArray())
                       // ->add('sku', TextField::class, TextFieldOption::make()->label(trans('plugins/ecommerce::products.sku')))
                        ->add(
                            'description',
                            EditorField::class,
                            EditorFieldOption::make()
                                ->label(trans('core/base::forms.description'))
                                ->placeholder(trans('core/base::forms.description_placeholder'))->toArray()
                        )
                        ->add('content', EditorField::class, ContentFieldOption::make() ->label(trans('Features'))->allowedShortcodes()->toArray())
                        ->add(
                            'warranty_information',
                            EditorField::class,
                            EditorFieldOption::make()
                                ->label(trans('warranty information'))
                                ->placeholder(trans('core/base::forms.description_placeholder'))->toArray()
                        )
                        // ->add('warranty_information', EditorField::class, ContentFieldOption::make()->allowedShortcodes()->toArray())
                        ->add('images[]', MediaImagesField::class, [
                            'label' => trans('plugins/ecommerce::products.form.image'),
                            'values' => $productId ? $this->getModel()->images : [],
                        ])

                //          ->addMetaBoxes([
                //     'Video' => [
                //         'title' => 'Product Videos',
                //         'content' => view('plugins/ecommerce::products.partials.video-upload', [
                //             // Decode the video paths JSON before passing to the view
                //             'videos' => !empty($this->getModel()->video_path) ? json_decode($this->getModel()->video_path, true) : [],
                //         ]),
                //         'priority' => 50,
                //     ],
                // ])
                                    
                                    ->addMetaBoxes([
                        'Video' => [
                            'title' => 'Product Videos',
                            'content' => view('plugins/ecommerce::products.partials.video-upload', [
                                // Decode the video paths JSON only if it is a string
                                'videos' => is_string($this->getModel()->video_path) && !empty($this->getModel()->video_path) 
                                    ? json_decode($this->getModel()->video_path, true) 
                                    : [],
                            ]),
                            'priority' => 50,
                        ],
                    ])
                //  ->addMetaBoxes([
                //                     'Video' => [
                //                         'title' => 'Product Videos',
                //                         'content' => view('plugins/ecommerce::products.partials.video-upload', [
                //                             // Decode the video paths JSON before passing to the view
                //                             'videos' => !empty($this->getModel()->video_path) ? json_decode($this->getModel()->video_path, true) : [],
                //                         ]),
                //                         'priority' => 550,
                //                     ],
                //                 ])

                ->addMetaBoxes([
                    'variants' => [
                        'title' => 'Product Variants',
                        'content' => view('plugins/ecommerce::products.partials.variants_form', [
                            'product' => $this->getModel(), // Pass the current product model for editing
                        ]),
                        'priority' => 150,
                    ],
             
                ])
                ->addMetaBoxes([
                    'comparison' => [
                        'title' => 'Product Comparison',
                        'content' => view('plugins/ecommerce::products.partials.comparison_form', [
                            'product' => $this->getModel(), // Pass the current product model for editing
                        ]),
                        'priority' => 150,
                    ],
                ])
                
                // ->addMetaBoxes([
                           
                //     'comparisons' => [
                //         'title' => 'Comparison Products',
                //         'content' => view('plugins/ecommerce::products.partials.comparison_form', [
                //             'comparisons' => $this->getModel()->comparisons ?? [], // Fetch existing comparisons if editing
                //             'products' => Product::pluck('sku', 'id')->toArray(), // Fetch SKUs for the dropdown
                //         ]),
                //         'priority' => 51,
                //     ],
                // ])


                    
                        
                        // ->add('video', 'file', [
                        //     'label' => trans('Upload Video'), // Label for the field
                        //     'accept' => 'video/*', // Accept video files only
                        //     'maxFileSize' => 51200, // Max file size in kilobytes (50MB)
                        // ])
                                
                 
                // ->add('existing_videos', 'customHtml', [

                //    'html' => view('plugins/ecommerce::products.partials.existing-videos')

                // ])
                
                // ->add('video', 'file', [
                //     'label' => trans('Upload New Videos'),
                //     'accept' => 'video/*', // Accept video files only
                //     'multiple' => true, // Allow multiple file uploads
                //     'maxFileSize' => 5000, // Max file size in kilobytes (5MB)
                // ])
                // ->addMetaBoxes([
                  
                //     'videos' => [ // Add a new meta box for videos
                //         'title' => 'Product Videos',
                //         'content' => view('plugins/ecommerce::products.partials.existing-videos', [
                //             'product' => $this->getModel(), // Pass the current product model
                //         ]),
                //         'priority' => 50,
                //     ],
                // ])
                
               
                        
                // ->addMetaBoxes([
                //     'Video' => [
                //         'title' => 'Product Videos',
                //         'content' => view('plugins/ecommerce::products.partials.video-upload', [
                //             'specs' => $this->getModel()->video_path ?? [], // Fetch existing specs if editing
                //         ]),
                //         'priority' => 50,
                //     ],
                // ])

               
                
                
                    

                        // ->addMetaBoxes([
                        //     'specs' => [
                        //         'title' => 'Add Specs',
                        //         'content' => new HtmlString(
                        //             Html::tag('div', 
                        //                 Html::tag('input', '', [
                        //                     'name' => 'specs_sheet_heading',
                        //                     'placeholder' => 'Specs Heading',
                        //                     'class' => 'form-control'
                        //                 ]) .
                        //                 Html::tag('div', '', [
                        //                     'class' => 'specs-items',
                        //                     'data-repeater-list' => 'specs_sheet',
                        //                 ]), 
                        //                 ['class' => 'specs-container']
                        //             )
                        //         ),
                        //         'wrap' => false,
                        //         'priority' => 1,
                        //     ],
                        // ])
                       
                        

                        ->addMetaBoxes([
                            'specs' => [
                                'title' => 'Specifications',
                                'content' => view('plugins/ecommerce::products.partials.specs-form', [
                                    'specs' => $this->getModel()->specifications ?? [], // Fetch existing specs if editing
                                ]),
                                'priority' => 50,
                            ],
                        ])

                        ->addMetaBoxes([
                                        'documents' => [
                                'title' => 'Product Documents',
                                'content' => view('plugins/ecommerce::products.partials.documents-form', [
                                    'documents' => $this->getModel()->documents ?? [], // Fetch existing documents if editing
                                ]),
                                'priority' => 60,
                            ],
                        ])

                        // ->add('compare_type', 'select', [
                        //     'label' => 'Comparison Type',
                        //     'options' => [
                        //         'good' => 'Good',
                        //         'better' => 'Better',
                        //         'best' => 'Best'
                        //     ],
                           
                        // ])
                        // // Comparison Products Dropdown
                        // ->add('compare_product', 'select', [
                        //     'label' => 'Comparison Products',
                        //     'options' => Product::pluck('sku', 'id')->toArray(), // Fetching SKUs from the ec_products table
                        //     'multiple' => true, // Allows multiple selections
                        // ])
                        


                                        // Add this to the meta boxes array in the ProductForm class
                        // Remove this block
                        //   ->addMetaBoxes([
                        //     'temp_product_changes' => [
                        //         'title' => 'Review Product Changes',
                        //         'content' => view('plugins/ecommerce::products.partials.product-changes-button', [
                        //             // You can pass additional data here if needed
                        //         ])->render(),
                        //         'wrap' => false,
                        //         'priority' => 100,
                        //     ],
                        // ])
                        


                        
                        ->add('product_type', 'hidden', [
                            'value' => request()->input('product_type') ?: ProductTypeEnum::PHYSICAL,
                        ])
                        ->add('status', SelectField::class, StatusFieldOption::make()->toArray())
                        ->add(
                            'is_featured',
                            OnOffField::class,
                            OnOffFieldOption::make()
                                ->label(trans('core/base::forms.is_featured'))
                                ->defaultValue(false)
                                ->toArray()
                        )
                    
                        ->add(
                            'categories[]',
                            TreeCategoryField::class,
                            SelectFieldOption::make()
                                ->label(trans('plugins/ecommerce::products.form.categories'))
                                ->choices(ProductCategoryHelper::getActiveTreeCategories())
                                ->selected(old('categories', $selectedCategories))
                                ->addAttribute('card-body-class', 'p-0')
                                ->toArray()
                        )
                        ->when($brands, function () use ($brands) {
                            $this
                                ->add(
                                    'brand_id',
                                    SelectField::class,
                                    SelectFieldOption::make()
                                        ->label(trans('plugins/ecommerce::products.form.brand'))
                                        ->choices($brands)
                                        ->searchable()
                                        ->emptyValue(trans('plugins/ecommerce::brands.select_brand'))
                                        ->allowClear()
                                        ->toArray()
                                );
                        })
                        ->add(
                            'image',
                            MediaImageField::class,
                            MediaImageFieldOption::make()
                                ->label(trans('plugins/ecommerce::products.form.featured_image'))
                                ->toArray()
                        )
                        ->when($productCollections, function () use ($productCollections) {
                            $selectedProductCollections = [];

                            if ($this->getModel() && $this->getModel()->getKey()) {
                                $selectedProductCollections = $this->getModel()
                                    ->productCollections()
                                    ->pluck('product_collection_id')
                                    ->all();
                            }

                            $this
                                ->add('product_collections[]', MultiCheckListField::class, [
                                'label' => trans('plugins/ecommerce::products.form.collections'),
                                'choices' => $productCollections,
                                'value' => old('product_collections', $selectedProductCollections),
                            ]);


                            
                        })
                        ->when($productLabels, function () use ($productLabels) {
                            $selectedProductLabels = [];

                            if ($this->getModel() && $this->getModel()->getKey()) {
                                $selectedProductLabels = $this->getModel()->productLabels()->pluck('product_label_id')->all();
                            }

                            $this
                                ->add('product_labels[]', MultiCheckListField::class, [
                                    'label' => trans('plugins/ecommerce::products.form.labels'),
                                    'choices' => $productLabels,
                                    'value' => old('product_labels', $selectedProductLabels),
                                ]);
                        })
                        ->when(EcommerceHelper::isTaxEnabled(), function () {
                            $taxes = Tax::query()->orderBy('percentage')->get()->pluck('title_with_percentage', 'id')->all();

                            if ($taxes) {
                                $selectedTaxes = [];
                                if ($this->getModel() && $this->getModel()->getKey()) {
                                    $selectedTaxes = $this->getModel()->taxes()->pluck('tax_id')->all();
                                } elseif ($defaultTaxRate = get_ecommerce_setting('default_tax_rate')) {
                                    $selectedTaxes = [$defaultTaxRate];
                                }

                                $this->add('taxes[]', MultiCheckListField::class, [
                                    'label' => trans('plugins/ecommerce::products.form.taxes'),
                                    'choices' => $taxes,
                                    'value' => old('taxes', $selectedTaxes),
                                ]);
                            }
                        })
                        ->when(EcommerceHelper::isCartEnabled(), function (ProductForm $form) {
                            $form
                                ->add(
                                    'minimum_order_quantity',
                                    NumberField::class,
                                    NumberFieldOption::make()
                                        ->label(trans('plugins/ecommerce::products.form.minimum_order_quantity'))
                                        ->helperText(trans('plugins/ecommerce::products.form.minimum_order_quantity_helper'))
                                        ->defaultValue(0)
                                        ->toArray()
                                )
                                ->add(
                                    'maximum_order_quantity',
                                    NumberField::class,
                                    NumberFieldOption::make()
                                        ->label(trans('plugins/ecommerce::products.form.maximum_order_quantity'))
                                        ->helperText(trans('plugins/ecommerce::products.form.maximum_order_quantity_helper'))
                                        ->defaultValue(0)
                                        ->toArray()
                                );
                        })
                        ->add('tag', TagField::class, [
                            'label' => trans('plugins/ecommerce::products.form.tags'),
                            'value' => $tags,
                            'attr' => [
                                'placeholder' => trans('plugins/ecommerce::products.form.write_some_tags'),
                                'data-url' => route('product-tag.all'),
                            ],
                        ])
                        ->add('producttypes', TagField::class, [
                            'label' => trans('plugins/ecommerce::products.form.producttypes'),
                            'value' => $producttypes,
                            'attr' => [
                                'placeholder' => trans('plugins/ecommerce::products.form.write_some_producttypes'),
                                'data-url' => route('product-types.all'),
                            ],
                        ])

                        ->add('frequently_bought_together', TagField::class, [
                            'label' => trans('plugins/ecommerce::products.form.frequently_bought_together'),
                            'attr' => [
                                'placeholder' => trans('plugins/ecommerce::products.form.search_sku'),
                                'data-url' => route('products.search-sku'), // ensure this route exists
                            ],
                            'value' => $frequently_bought_together, // fetch the value from the request or model
                        ])
                        
                        // ->addMetaBoxes([
                        //     'admin_reviews' => [
                        //         'title' => 'Admin Reviews',
                        //         'content' => view('plugins/ecommerce::products.partials.admin-reviews', [
                        //             'reviews' => Review::where('product_id', $this->getModel()->id)
                        //                               ->whereNotNull('star')
                        //                               ->when(auth('web')->check() && auth('web')->user()->is_admin, function($query) {
                        //                                   // Adjust the condition based on how you identify admin users
                        //                                   $query->whereHas('user', function($query) {
                        //                                       $query->whereIn('id', User::where('is_admin', true)->pluck('id'));
                        //                                   });
                        //                               })
                        //                               ->get()
                        //         ])->render(), // Ensure the content is a string
                        //         'wrap' => false,
                        //         'priority' => 6,
                        //     ],
                        //     // 'add_testimonials' => [
                        //     //     'title' => 'Add Testimonials',
                        //     //     'content' => view('plugins/ecommerce::products.partials.Testimonials-form', [
                        //     //         'testimonials' => $this->getModel()->reviews ?? []
                        //     //     ])->render(), // Ensure the content is a string
                        //     //     'wrap' => false,
                        //     //     'priority' => 7,
                        //     // ],
                        // ])
                        
                        


                        //->add('handle', 'text', ['label' => 'Handle'])
                    // ->add('variant_grams', 'text', ['label' => 'Variant Grams'])
                    // ->add('variant_inventory_tracker', 'text', ['label' => 'Variant Inventory Tracker'])
                    // ->add('variant_inventory_quantity', 'number', ['label' => 'Variant Inventory Quantity'])
                    // ->add('variant_inventory_policy', 'text', ['label' => 'Variant Inventory Policy'])
                    // ->add('variant_fulfillment_service', 'text', ['label' => 'Variant Fulfillment Service'])
                    // ->add('variant_requires_shipping', 'text', ['label' => 'Variant Grams'])
                    // ->add('variant_barcode', 'text', ['label' => 'Variant Barcode'])
                    // ->add('gift_card', 'text', ['label' => 'Variant Grams'])
                    // ->add('seo_title', 'text', ['label' => 'SEO Title'])
                    // ->add('seo_description', 'textarea', ['label' => 'SEO Description'])
                    ->add('variant_requires_shipping', 'select', [
                        'label' => 'Variant Requires Shipping',
                        'choices' => [
                            1 => 'Yes',   // True
                            0 => 'No'     // False
                        ],
                      
                        'attr' => ['class' => 'form-control']
                    ])
                    // ->add('Refund Policy', 'select', [
                    //     'label' => 'Refund Policy',
                    //     'choices' => [
                    //         0 => '15 Days',   
                    //         1 => '90 Days' ,    
                    //         2 => 'Non Refundable'    
                    //     ],
                    //     'selected' => $model->refund ?? 2,  // Set default selected value if needed
                    //     'attr' => ['class' => 'form-control']

                        
                    // ])
                    // ->add('refund', 'text', ['label' => 'Refund Policy'])
                    ->add('refund', 'select', [
                        'label' => 'Refund Policy',
                        'choices' => [
                            'non-refundable' => 'Non-refundable',
                            '15 days' => '15 Days Refund',
                            '90 days' => '90 Days Refund',
                        ],
                        
                        'attr' => [
                            'class' => 'form-control',
                        ],
                        
                    ])
                    
                    ->add('google_shopping_category', 'text', ['label' => 'Google Shopping / Google Product Category'])
                    
                 ->add('unit_of_measurement_id', 'select', [
                        'label' => 'Unit of Measurement',
                        'choices' => UnitOfMeasurement::pluck('name', 'id')->toArray(), // Fetch the list of units from the DB
                        'empty_value' => 'Select a Unit' // Optional placeholder
                    ])
                    ->add('delivery_days', 'text', ['label' => 'Delivery Days'])

                    // ->add('google_shopping_gender', 'text', ['label' => 'Google Shopping / Gender'])
                    // ->add('google_shopping_age_group', 'text', ['label' => 'Google Shopping / Age Group'])
                    ->add('google_shopping_mpn', 'text', ['label' => 'Google Shopping / MPN'])
                    // ->add('google_shopping_condition', 'text', ['label' => 'Google Shopping / Condition'])
                    // ->add('google_shopping_custom_product', 'text', ['label' => 'Google Shopping / Custom Product'])
                    // ->add('google_shopping_custom_label_0', 'text', ['label' => 'Google Shopping / Custom Label 0'])
                    // ->add('google_shopping_custom_label_1', 'text', ['label' => 'Google Shopping / Custom Label 1'])
                    // ->add('google_shopping_custom_label_2', 'text', ['label' => 'Google Shopping / Custom Label 2'])
                    // ->add('google_shopping_custom_label_3', 'text', ['label' => 'Google Shopping / Custom Label 3'])
                    // ->add('google_shopping_custom_label_4', 'text', ['label' => 'Google Shopping / Custom Label 4'])
                    ->add('box_quantity', 'number', ['label' => 'Box Quantity'])
                    // ->add('technical_table', 'text', ['label' => 'Technical Table'])
                    // ->add('technical_spec', 'text', ['label' => 'Technical Spec'])
                    ->add('product_label', 'text', ['label' => 'Product Label'])


                        // ->addMetaBoxes([
                        //     'add_specs' => [
                        //         'title' => 'Add Specs',
                        //         'content' => view('plugins/ecommerce::products.partials.add-specs-form'),
                        //         'priority' => 5,
                        //         'before_wrapper' => '<div id="specs-wrapper" style="display: flex; flex-wrap: wrap; gap: 10px;">',
                        //         'after_wrapper' => '</div>',
                        //     ],
                        // ])

                        ->addMetaBoxes([
                            'shipping_weight' => [
                                'title' => 'Shipping Weight',
                                'content' => view('plugins/ecommerce::products.partials.shipping-weight-form', [
                                    'shipping_weight' => $this->getModel()->shipping_weight ?? null, // Fetch existing shipping weight if editing
                                    'shipping_weight_option' => $this->getModel()->shipping_weight_option ?? null, // Fetch existing shipping weight option
                                ]),
                                'priority' => 50,
                            ],
                        ])
                
                        ->setBreakFieldPoint('status');
                      // Check if the product and video path are set
               // Check if the product and video path are set
                     

   
                    if (EcommerceHelper::isEnabledProductOptions()) {
                        $this
                            ->addMetaBoxes([
                                'product_options_box' => [
                                    'title' => trans('plugins/ecommerce::product-option.name'),
                                    'content' => view('plugins/ecommerce::products.partials.product-option-form', [
                                        'options' => GlobalOptionEnum::options(),
                                        'globalOptions' => GlobalOption::query()->pluck('name', 'id')->all(),
                                        'product' => $this->getModel(),
                                        'routes' => [
                                            'ajax_option_info' => route('global-option.ajaxInfo'),
                                        ],
                                    ]),
                                    'priority' => 4,
                                ],
                            ]);
                    }

                    $productAttributeSets = ProductAttributeSet::getAllWithSelected($productId, []);

                    $this
                        ->addMetaBoxes([
                            'attribute-sets' => [
                                'content' => '',
                                'before_wrapper' => '<div class="d-none product-attribute-sets-url" data-url="' . route('products.product-attribute-sets') . '">',
                                'after_wrapper' => '</div>',
                                'priority' => 3,
                            ],
                        ]);

                    if (! $totalProductVariations) {
                        $this
                            ->removeMetaBox('variations')
                            ->addMetaBoxes([
                                'general' => [
                                    'title' => trans('plugins/ecommerce::products.overview'),
                                    'content' => view(
                                        'plugins/ecommerce::products.partials.general',
                                        [
                                            'product' => $productId ? $this->getModel() : null,
                                            'isVariation' => false,
                                            'originalProduct' => null,
                                        ]
                                    ),
                                    'before_wrapper' => '<div id="main-manage-product-type">',
                                    'priority' => 2,
                                ],
                                'attributes' => [
                                    'title' => trans('plugins/ecommerce::products.attributes'),
                                    'content' => view('plugins/ecommerce::products.partials.add-product-attributes', [
                                        'product' => $this->getModel(),
                                        'productAttributeSets' => $productAttributeSets,
                                        'addAttributeToProductUrl' => $this->getModel()->id
                                            ? route('products.add-attribute-to-product', $this->getModel()->id)
                                            : null,
                                    ]),
                                    'header_actions' => $productAttributeSets->isNotEmpty()
                                        ? view('plugins/ecommerce::products.partials.product-attribute-actions')
                                        : null,
                                    'after_wrapper' => '</div>',
                                    'priority' => 3,
                                ],
                            ]);
                    } elseif ($productId) {
                        $productVariationTable = app(ProductVariationTable::class)
                            ->setProductId($productId)
                            ->setProductAttributeSets($productAttributeSets);

                        if (EcommerceHelper::isEnabledSupportDigitalProducts() && $this->getModel()->isTypeDigital()) {
                            $productVariationTable->isDigitalProduct();
                        }

                        $this
                            ->removeMetaBox('general')
                            ->addMetaBoxes([
                                'variations' => [
                                    'title' => trans('plugins/ecommerce::products.product_has_variations'),
                                    'content' => view('plugins/ecommerce::products.partials.configurable', [
                                        'product' => $this->getModel(),
                                        'productAttributeSets' => $productAttributeSets,
                                        'productVariationTable' => $productVariationTable,
                                    ]),
                                    'header_actions' => view(
                                        'plugins/ecommerce::products.partials.product-variation-actions',
                                        ['product' => $this->getModel()]
                                    ),
                                    'has_table' => true,
                                    'before_wrapper' => '<div id="main-manage-product-type">',
                                    'after_wrapper' => '</div>',
                                    'priority' => 3,
                                    'render' => false,
                                ],
                            ])
                            ->addAfter('brand_id', 'sku', TextField::class, TextFieldOption::make()->label(trans('plugins/ecommerce::products.sku')));
                    }

                    if ($productId ) {
                        add_filter('base_action_form_actions_extra', function () {
                            return view('plugins/ecommerce::forms.duplicate-action', ['product' => $this->getModel()])->render();
                        });
                    }

                
        }
    }


    public function addAssets(): void
    {
        Assets::addStyles('datetimepicker')
            ->addScripts([
                'moment',
                'datetimepicker',
                'input-mask',
                'jquery-ui',
            ])
            ->addStylesDirectly('vendor/core/plugins/ecommerce/css/ecommerce.css')
            ->addScriptsDirectly('vendor/core/plugins/ecommerce/js/edit-product.js');
    }

  
}
