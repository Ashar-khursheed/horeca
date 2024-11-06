<?php

namespace Botble\Ecommerce\Services;

use Botble\Base\Events\CreatedContentEvent;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductTypes;
use Illuminate\Http\Request;

class StoreProductTypesService
{
    public function execute(Request $request, Product $product): void
    {
        if (! $request->has('producttypes')) {
            return;
        }

        $producttypes = $product->producttypes->pluck('name')->all();

        $producttypesInput = collect(json_decode((string) $request->input('producttypes'), true))->pluck('value')->all();

        if (count($producttypes) != count($producttypesInput) || count(array_diff($producttypes, $producttypesInput)) > 0) {
            $product->producttypes()->detach();

            $producttypesIds = [];

            foreach ($producttypesInput as $producttypesName) {
                if (! trim($producttypesName)) {
                    continue;
                }

                $producttypes= ProductTypes::query()->where('name', $producttypesName)->first();

                if ($producttypes=== null && ! empty($producttypesName)) {
                    $producttypes= ProductTypes::query()->create(['name' => $producttypesName]);

                    $request->merge(['slug' => $producttypesName]);

                    event(new CreatedContentEvent(PRODUCT_TYPES_MODULE_SCREEN_NAME, $request, $producttypes));
                }

                if (! empty($producttypes)) {
                    $producttypesIds[] = $producttypes->getKey();
                }
            }

            $product->producttypes()->sync(array_unique($producttypesIds));
        }
    }
}
