<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderApiController extends Controller
{
    // Store a new order
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:ec_customers,id',
            'products' => 'required|array',
            'products.*.product_id' => 'required|exists:ec_products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'shipping_method' => 'required|string',
            // Add more validation rules as necessary
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $order = new Order();
        $order->user_id = $request->user_id;
        // Assign other properties
        $order->shipping_method = $request->shipping_method;

        // Calculate amounts, tax, etc.
        $order->amount = $this->calculateTotalAmount($request->products);
        $order->save();

        // Attach products to the order
        foreach ($request->products as $product) {
            $order->products()->attach($product['product_id'], ['qty' => $product['quantity']]);
        }

        return response()->json($order, 201);
    }

    // Calculate total amount based on products
    private function calculateTotalAmount(array $products): float
    {
        // This function should calculate total amount based on the products provided.
        $total = 0.0;
        foreach ($products as $product) {
            $productModel = Product::find($product['product_id']);
            $total += $productModel->price * $product['quantity'];
        }
        return $total;
    }

    // Fetch all orders for a user
    public function index(Request $request)
    {
        $orders = Order::where('user_id', $request->user()->id)->get();
        return response()->json($orders);
    }

    // Get a specific order
    public function show($id)
    {
        $order = Order::findOrFail($id);
        return response()->json($order);
    }

    // Update order status
    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $order->status = $request->status;
        $order->save();

        return response()->json($order);
    }

    // Delete an order
    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();

        return response()->json(null, 204);
    }
}
