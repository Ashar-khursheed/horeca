{!! apply_filters('ecommerce_product_variation_form_start', null, $product) !!}
@php
  $user = Auth::user(); // Get the logged-in user
    $productspec = DB::table('role_users')
        ->where('user_id', $user->id)
        ->where('role_id', 6)
        ->exists(); 
@endphp
@if (!$productspec)
 


<div class="row price-group">
    <input
        class="detect-schedule d-none"
        name="sale_type"
        type="hidden"
        value="{{ old('sale_type', $product ? $product->sale_type : 0) }}"
    >

    <div class="col-md-4">
        <x-core::form.text-input
            :label="trans('plugins/ecommerce::products.sku')"
            name="sku"
            :value="old('sku', $product ? $product->sku : (new Botble\Ecommerce\Models\Product()))"
        />

        @if (($isVariation && !$product) || ($product && $product->is_variation && !$product->sku))
            <x-core::form.checkbox
                :label="trans('plugins/ecommerce::products.form.auto_generate_sku')"
                name="auto_generate_sku"
            />
        @endif
    </div>

    <div class="col-md-4">
        <x-core::form.text-input
            :label="trans('plugins/ecommerce::products.form.price')"
            name="price"
            :data-thousands-separator="EcommerceHelper::getThousandSeparatorForInputMask()"
            :data-decimal-separator="EcommerceHelper::getDecimalSeparatorForInputMask()"
            :value="old('price', $product ? $product->price : $originalProduct->price ?? 0)"
            step="any"
            class="input-mask-number"
            :group-flat="true"
        >
            <x-slot:prepend>
                <span class="input-group-text">{{ get_application_currency()->symbol }}</span>
            </x-slot:prepend>
        </x-core::form.text-input>
    </div>
    <div class="col-md-4">
        <x-core::form.text-input
            :label="trans('plugins/ecommerce::products.form.price_sale')"
            class="input-mask-number"
            name="sale_price"
            :data-thousands-separator="EcommerceHelper::getThousandSeparatorForInputMask()"
            :data-decimal-separator="EcommerceHelper::getDecimalSeparatorForInputMask()"
            :value="old('sale_price', $product ? $product->sale_price : $originalProduct->sale_price ?? null)"
            :group-flat="true"
            :data-sale-percent-text="trans('plugins/ecommerce::products.form.price_sale_percent_helper')"
        >
            <x-slot:helper-text>
                {!! trans('plugins/ecommerce::products.form.price_sale_percent_helper', ['percent' => '<strong>' . ($product ? $product->sale_percent : 0) . '%</strong>']) !!}
            </x-slot:helper-text>

            <x-slot:prepend>
                <span class="input-group-text">{{ get_application_currency()->symbol }}</span>
            </x-slot:prepend>
            <x-slot:labelDescription>
                <a
                    class="turn-on-schedule"
                    @style(['display: none' => old('sale_type', $product ? $product->sale_type : $originalProduct->sale_type ?? 0) == 1])
                    href="javascript:void(0)"
                >
                    {{ trans('plugins/ecommerce::products.form.choose_discount_period') }}
                </a>
                <a
                    class="turn-off-schedule"
                    @style(['display: none' => old('sale_type', $product ? $product->sale_type : $originalProduct->sale_type ?? 0) == 0])
                    href="javascript:void(0)"
                >
                    {{ trans('plugins/ecommerce::products.form.cancel') }}
                </a>
            </x-slot:labelDescription>
        </x-core::form.text-input>
    </div>

    <div class="col-md-6 scheduled-time" @style(['display: none' => old('sale_type', $product ? $product->sale_type : $originalProduct->sale_type ?? 0) == 0])>
        <x-core::form.text-input
            :label="trans('plugins/ecommerce::products.form.date.start')"
            name="start_date"
            class="form-date-time"
            :value="old('start_date', $product ? $product->start_date : $originalProduct->start_date ?? null)"
            :placeholder="BaseHelper::getDateTimeFormat()"
        />
    </div>
    <div class="col-md-6 scheduled-time" @style(['display: none' => old('sale_type', $product ? $product->sale_type : $originalProduct->sale_type ?? 0) == 0])>
        <x-core::form.text-input
            :label="trans('plugins/ecommerce::products.form.date.end')"
            name="end_date"
            :value="old('end_date', $product ? $product->end_date : $originalProduct->end_date ?? null)"
            :placeholder="BaseHelper::getDateTimeFormat()"
            class="form-date-time"
        />
    </div>

    <div class="col-md-6">
        <x-core::form.text-input
            :label="trans('plugins/ecommerce::products.form.cost_per_item')"
            name="cost_per_item"
            :value="old('cost_per_item', $product ? $product->cost_per_item : $originalProduct->cost_per_item ?? 0)"
            :placeholder="trans('plugins/ecommerce::products.form.cost_per_item_placeholder')"
            step="any"
            class="input-mask-number"
            :group-flat="true"
            :helper-text="trans('plugins/ecommerce::products.form.cost_per_item_helper')"
        >
            <x-slot:prepend>
                <span class="input-group-text">{{ get_application_currency()->symbol }}</span>
            </x-slot:prepend>
        </x-core::form.text-input>
    </div>
    <input
        name="product_id"
        type="hidden"
        value="{{ $product->id ?? null }}"
    >
    <div class="col-md-6">
        <x-core::form.text-input
            :label="trans('plugins/ecommerce::products.form.barcode')"
            name="barcode"
            type="text"
            :value="old('barcode', $product ? $product->barcode : $originalProduct->barcode ?? null)"
            step="any"
            :placeholder="trans('plugins/ecommerce::products.form.barcode_placeholder')"
        />
    </div>
</div>

{!! apply_filters('ecommerce_product_variation_form_middle', null, $product) !!}

<x-core::form.on-off.checkbox
    :label="trans('plugins/ecommerce::products.form.storehouse.storehouse')"
    name="with_storehouse_management"
    class="storehouse-management-status"
    :checked="old('with_storehouse_management', $product ? $product->with_storehouse_management : $originalProduct->with_storehouse_management ?? 0) == 1"
/>

<x-core::form.fieldset class="storehouse-info" @style(['display: none' => old('with_storehouse_management', $product ? $product->with_storehouse_management : $originalProduct->with_storehouse_management ?? 0) == 0])>
    <x-core::form.text-input
        :label="trans('plugins/ecommerce::products.form.storehouse.quantity')"
        name="quantity"
        :value="old('quantity', $product ? $product->quantity : $originalProduct->quantity ?? 0)"
        class="input-mask-number"
    />

    <x-core::form.on-off.checkbox
        :label="trans('plugins/ecommerce::products.form.stock.allow_order_when_out')"
        name="allow_checkout_when_out_of_stock"
        :checked="old('allow_checkout_when_out_of_stock', $product ? $product->allow_checkout_when_out_of_stock : $originalProduct->allow_checkout_when_out_of_stock ?? 0) == 1"
    />
</x-core::form.fieldset>

<x-core::form.fieldset class="stock-status-wrapper" @style(['display: none' => old('with_storehouse_management', $product ? $product->with_storehouse_management : $originalProduct->with_storehouse_management ?? 0) == 1])>
    <x-core::form.label for="stock_status">
        {{ trans('plugins/ecommerce::products.form.stock_status') }}
    </x-core::form.label>
    @foreach (Botble\Ecommerce\Enums\StockStatusEnum::labels() as $status => $label)
        <x-core::form.checkbox
            :label="$label"
            name="stock_status"
            type="radio"
            :value="$status"
            :checked="old('stock_status', $product ? $product->stock_status : 'in_stock') == $status"
            :inline="true"
        />
    @endforeach
</x-core::form.fieldset>
@endif
@if (
    ! EcommerceHelper::isEnabledSupportDigitalProducts()
    || (!$product && !$originalProduct &&  request()->input('product_type') != Botble\Ecommerce\Enums\ProductTypeEnum::DIGITAL)
    || ($originalProduct && $originalProduct->isTypePhysical()) || ($product && $product->isTypePhysical())
)
    <x-core::form.fieldset>
        <legend>
            <h3>Product fields</h3>
        </legend>
        <div class="row">
           
            
            <div class="col-md-3 col-md-6">
                <x-core::form.text-input
                    label="{{ trans('plugins/ecommerce::products.form.shipping.weight') }} "
                    name="weight"
                    :value="old('weight', $product->weight ?? $originalProduct->weight ?? 0)"
                    class="input-mask-number"
                    :group-flat="true"
                >
                    <x-slot:prepend>
                        <!-- Call the function and bind it to 'weight_unit_id' -->
                        <span class="input-group-text">
                            {!! ecommerce_unit_dropdown('weight_unit_id', old('weight_unit_id', $product->weight_unit_id ?? null)) !!}
                        </span>
                    </x-slot:prepend>
                </x-core::form.text-input>
            </div>
            <div class="col-md-3 col-md-6">
                <x-core::form.text-input
                    label="{{ trans('plugins/ecommerce::products.form.shipping.length') }} "
                    name="length"
                    :value="old('length', $product->length ?? $originalProduct->length ?? 0)"
                    class="input-mask-number"
                    :group-flat="true"
                >
                    <x-slot:prepend>
                        <!-- Call the function and bind it to 'length_unit_id' -->
                        <span class="input-group-text">
                            {!! ecommerce_unit_dropdown('length_unit_id', old('length_unit_id', $product->length_unit_id ?? null)) !!}
                        </span>
                    </x-slot:prepend>
                </x-core::form.text-input>
            </div>

            <div class="col-md-3 col-md-6">
                <x-core::form.text-input
                    label="{{ trans('plugins/ecommerce::products.form.shipping.depth') }} "
                    name="depth"
                    :value="old('depth', $product->depth ?? $originalProduct->depth ?? 0)"
                    class="input-mask-number"
                    :group-flat="true"
                >
                    <x-slot:prepend>
                        <!-- Call the function and bind it to 'depth_unit_id' -->
                        <span class="input-group-text">
                            {!! ecommerce_unit_dropdown('depth_unit_id', old('depth_unit_id', $product->depth_unit_id ?? null)) !!}
                        </span>
                    </x-slot:prepend>
                </x-core::form.text-input>
            </div>

           
            {{-- <div class="col-md-3 col-md-6">
                <x-core::form.text-input
                    label="{{ trans('plugins/ecommerce::products.form.shipping.depth') }} "
                    name="depth"
                    :value="old('depth', $product ? $product->depth : $originalProduct->depth ?? 0)"
                    class="input-mask-number"
                    :group-flat="true"
                >
                
                      
                 <x-slot:prepend> 
                    <span class="input-group-text">{!! ecommerce_width_height_unit(true) !!} <!-- Render dropdown --></span>
                </x-slot:prepend>
                </x-core::form.text-input>
            </div> --}}
            {{-- <div class="col-md-3 col-md-6">
                <x-core::form.text-input
                    label="{{ trans('plugins/ecommerce::products.form.shipping.width') }} ({{ ecommerce_width_height_unit() }})"
                    name="width"
                    :value="old('width', $product ? $product->width : $originalProduct->width ?? 0)"
                    class="input-mask-number"
                    :group-flat="true"
                >
                    <x-slot:prepend>
                        <span class="input-group-text">{{ ecommerce_width_height_unit() }}</span>
                    </x-slot:prepend>
                </x-core::form.text-input>
            </div> --}}
            <div class="col-md-3 col-md-6">
                <x-core::form.text-input
                    label="{{ trans('plugins/ecommerce::products.form.shipping.height') }} "
                    name="height"
                    :value="old('height', $product->height ?? $originalProduct->height ?? 0)"
                    class="input-mask-number"
                    :group-flat="true"
                >
                    <x-slot:prepend>
                        <!-- Call the function and bind it to 'depth_unit_id' -->
                        <span class="input-group-text">
                            {!! ecommerce_unit_dropdown('height_unit_id', old('height_unit_id', $product->height_unit_id ?? null)) !!}
                        </span>
                    </x-slot:prepend>
                </x-core::form.text-input>
            </div>
            <div class="col-md-3 col-md-6">
                <x-core::form.text-input
                    label="{{ trans('plugins/ecommerce::products.form.shipping.width') }} "
                    name="width"
                    :value="old('width', $product->width ?? $originalProduct->width ?? 0)"
                    class="input-mask-number"
                    :group-flat="true"
                >
                    <x-slot:prepend>
                        <!-- Call the function and bind it to 'depth_unit_id' -->
                        <span class="input-group-text">
                            {!! ecommerce_unit_dropdown('width_unit_id', old('width_unit_id', $product->width_unit_id ?? null)) !!}
                        </span>
                    </x-slot:prepend>
                </x-core::form.text-input>
            </div>

            
            
            
           
        </div>
    </x-core::form.fieldset>


    <x-core::form.fieldset>
        <legend>
            <h3>{{ trans('plugins/ecommerce::products.form.shipping.title') }}</h3>
        </legend>
        <div class="row">
           
            
           
            <div class="col-md-3 col-md-6">
                <x-core::form.text-input
                    label="{{ trans('plugins/ecommerce::products.form.shipping.shipping_length') }} "
                    name="shipping length"
                    :value="old('shipping_length', $product->shipping_length ?? $originalProduct->shipping_length ?? 0)"
                    class="input-mask-number"
                    :group-flat="true"
                >
                    <x-slot:prepend>
                        <!-- Call the function and bind it to 'length_unit_id' -->
                        <span class="input-group-text">
                            {!! ecommerce_unit_dropdown('shipping_length_id', old('shipping_length_id', $product->shipping_length_id ?? null)) !!}
                        </span>
                    </x-slot:prepend>
                </x-core::form.text-input>
            </div>

            <div class="col-md-3 col-md-6">
                <x-core::form.text-input
                    label="{{ trans('plugins/ecommerce::products.form.shipping.shipping_depth') }} "
                    name="shipping depth"
                    :value="old('shipping_depth', $product->shipping_depth ?? $originalProduct->shipping_depth ?? 0)"
                    class="input-mask-number"
                    :group-flat="true"
                >
                    <x-slot:prepend>
                        <!-- Call the function and bind it to 'depth_unit_id' -->
                        <span class="input-group-text">
                            {!! ecommerce_unit_dropdown('shipping_depth_id', old('shipping_depth_id', $product->shipping_depth_id ?? null)) !!}
                        </span>
                    </x-slot:prepend>
                </x-core::form.text-input>
            </div>

           
            {{-- <div class="col-md-3 col-md-6">
                <x-core::form.text-input
                    label="{{ trans('plugins/ecommerce::products.form.shipping.depth') }} "
                    name="depth"
                    :value="old('depth', $product ? $product->depth : $originalProduct->depth ?? 0)"
                    class="input-mask-number"
                    :group-flat="true"
                >
                
                      
                 <x-slot:prepend> 
                    <span class="input-group-text">{!! ecommerce_width_height_unit(true) !!} <!-- Render dropdown --></span>
                </x-slot:prepend>
                </x-core::form.text-input>
            </div> --}}
            {{-- <div class="col-md-3 col-md-6">
                <x-core::form.text-input
                    label="{{ trans('plugins/ecommerce::products.form.shipping.width') }} ({{ ecommerce_width_height_unit() }})"
                    name="width"
                    :value="old('width', $product ? $product->width : $originalProduct->width ?? 0)"
                    class="input-mask-number"
                    :group-flat="true"
                >
                    <x-slot:prepend>
                        <span class="input-group-text">{{ ecommerce_width_height_unit() }}</span>
                    </x-slot:prepend>
                </x-core::form.text-input>
            </div> --}}
            <div class="col-md-3 col-md-6">
                <x-core::form.text-input
                    label="{{ trans('plugins/ecommerce::products.form.shipping.shipping_height') }} "
                    name="shipping height"
                    :value="old('shipping_height', $product->shipping_height ?? $originalProduct->shipping_height ?? 0)"
                    class="input-mask-number"
                    :group-flat="true"
                >
                    <x-slot:prepend>
                        <!-- Call the function and bind it to 'depth_unit_id' -->
                        <span class="input-group-text">
                            {!! ecommerce_unit_dropdown('shipping_height_id', old('shipping_height_id', $product->shipping_height_id ?? null)) !!}
                        </span>
                    </x-slot:prepend>
                </x-core::form.text-input>
            </div>
            <div class="col-md-3 col-md-6">
                <x-core::form.text-input
                    label="{{ trans('plugins/ecommerce::products.form.shipping.shipping_width') }} "
                    name="shipping_width"
                    :value="old('shipping_width', $product->shipping_width ?? $originalProduct->shipping_width ?? 0)"
                    class="input-mask-number"
                    :group-flat="true"
                >
                    <x-slot:prepend>
                        <!-- Call the function and bind it to 'depth_unit_id' -->
                        <span class="input-group-text">
                            {!! ecommerce_unit_dropdown('shipping_width_id', old('shipping_width_id', $product->shipping_width_id ?? null)) !!}
                        </span>
                    </x-slot:prepend>
                </x-core::form.text-input>
            </div>

            
            
            
           
        </div>
    </x-core::form.fieldset>
@endif

@if (
    EcommerceHelper::isEnabledSupportDigitalProducts()
    && (
        (!$product &&  !$originalProduct && request()->input('product_type') == Botble\Ecommerce\Enums\ProductTypeEnum::DIGITAL)
        || ($originalProduct && $originalProduct->isTypeDigital()) || ($product && $product->isTypeDigital())
    )
)
    <x-core::form.on-off.checkbox
        :label="trans('plugins/ecommerce::products.digital_attachments.generate_license_code_after_purchasing_product')"
        name="generate_license_code"
        :checked="old('generate_license_code', $product ? $product->generate_license_code : $originalProduct->generate_license_code ?? 0)"
    />

    <x-core::form-group class="product-type-digital-management">
        <x-core::form.label for="product_file" class="mb-3">
            {{ trans('plugins/ecommerce::products.digital_attachments.title') }}

            <x-slot:description>
                <div class="btn-list">
                    <x-core::button type="button" class="digital_attachments_btn" size="sm" icon="ti ti-paperclip">
                        {{ trans('plugins/ecommerce::products.digital_attachments.add') }}
                    </x-core::button>

                    <x-core::button type="button" class="digital_attachments_external_btn" size="sm" icon="ti ti-link">
                        {{ trans('plugins/ecommerce::products.digital_attachments.add_external_link') }}
                    </x-core::button>
                </div>
            </x-slot:description>
        </x-core::form.label>

        <x-core::table>
            <x-core::table.header>
                <x-core::table.header.cell />
                <x-core::table.header.cell>
                    {{ trans('plugins/ecommerce::products.digital_attachments.file_name') }}
                </x-core::table.header.cell>
                <x-core::table.header.cell>
                    {{ trans('plugins/ecommerce::products.digital_attachments.file_size') }}
                </x-core::table.header.cell>
                <x-core::table.header.cell>
                    {{ trans('core/base::tables.created_at') }}
                </x-core::table.header.cell>
                <x-core::table.header.cell />
            </x-core::table.header>

            <x-core::table.body>
                @if($product)
                    @foreach ($product->productFiles as $file)
                        <x-core::table.body.row>
                            <x-core::table.body.cell>
                                <x-core::form.on-off.checkbox
                                    name="product_files[{{ $file->id }}]"
                                    class="digital-attachment-checkbox"
                                    :checked="true"
                                    :single="true"
                                />
                            </x-core::table.body.cell>
                            <x-core::table.body.cell>
                                @if ($file->is_external_link)
                                    <a href="{{ $file->url }}" target="_blank">
                                        <x-core::icon name="ti ti-link" />
                                        {{ $file->basename ? Str::limit($file->basename, 50) : $file->url }}
                                    </a>
                                @else
                                    <x-core::icon name="ti ti-paperclip" />
                                    {{ Str::limit($file->basename, 50) }}
                                @endif
                            </x-core::table.body.cell>
                            <x-core::table.body.cell>
                                {{ $file->file_size ? BaseHelper::humanFileSize($file->file_size) : '-' }}
                            </x-core::table.body.cell>
                            <x-core::table.body.cell>
                                {{ BaseHelper::formatDate($file->created_at) }}
                            </x-core::table.body.cell>
                            <x-core::table.body.cell />
                        </x-core::table.body.row>
                    @endforeach
                @endif
            </x-core::table.body>
        </x-core::table>

        <div class="digital_attachments_input">
            <input
                name="product_files_input[]"
                data-id="{{ Str::random(10) }}"
                type="file"
            >
        </div>
    </x-core::form-group>

    @if (request()->ajax())
        @include('plugins/ecommerce::products.partials.digital-product-file-template')
    @else
        @pushOnce('footer')
            @include('plugins/ecommerce::products.partials.digital-product-file-template')
        @endpushOnce
    @endif
@endif

{!! apply_filters('ecommerce_product_variation_form_end', null, $product) !!}
