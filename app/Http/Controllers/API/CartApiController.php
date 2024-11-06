<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Botble\Ecommerce\Models\Cart;
use Botble\Ecommerce\Models\Product;



class CartApiController extends Controller
{
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:ec_products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $productId = $request->input('product_id');
        $quantity = $request->input('quantity');

        if (Auth::check()) {
            // Logged-in user
            $userId = Auth::id();
            $cartItem = Cart::updateOrCreate(
                ['user_id' => $userId, 'product_id' => $productId],
                ['quantity' => \DB::raw("quantity + $quantity")]
            );
        } 
        // else {
        //     // Guest user
        //     $sessionId = $request->session()->getId();
        //     $cartItem = Cart::updateOrCreate(
        //         ['session_id' => $sessionId, 'product_id' => $productId],
        //         ['quantity' => \DB::raw("quantity + $quantity")]
        //     );
        // }
        
        $cartItem = Cart::find($cartItem->id); // Get the cart item again with updated values

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $cartItem->id,
                'user_id' => $cartItem->user_id,
                'session_id' => $cartItem->session_id,
                'product_id' => $cartItem->product_id,
                'quantity' => $cartItem->quantity,
                'created_at' => $cartItem->created_at,
                'updated_at' => $cartItem->updated_at,
            ],
        ]);
    }

    public function viewCart(Request $request)
    {
        $cartItems = Auth::check() 
            ? Cart::where('user_id', Auth::id())->with('product')->get() 
            : Cart::where('session_id', $request->session()->getId())->with('product')->get();

        return response()->json([
            'success' => true,
            'data' => $cartItems,
        ]);
    }

    public function clearCart(Request $request)
    {
        if (Auth::check()) {
            Cart::where('user_id', Auth::id())->delete();
        } else {
            Cart::where('session_id', $request->session()->getId())->delete();
        }

        return response()->json(['success' => true]);
    }

    // Update cart quantity method
    public function updateCartQuantity(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:ec_products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $productId = $request->input('product_id');
        $quantity = $request->input('quantity');

        if (Auth::check()) {
            $userId = Auth::id();
            $cartItem = Cart::where('user_id', $userId)->where('product_id', $productId)->first();
        } else {
            $sessionId = $request->session()->getId();
            $cartItem = Cart::where('session_id', $sessionId)->where('product_id', $productId)->first();
        }

        if ($cartItem) {
            $cartItem->quantity = $quantity;
            $cartItem->save();

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $cartItem->id,
                    'user_id' => $cartItem->user_id,
                    'session_id' => $cartItem->session_id,
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'created_at' => $cartItem->created_at,
                    'updated_at' => $cartItem->updated_at,
                ],
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Item not found in cart.'], 404);
    }

    // Additional methods for guest users
    // public function addToCartGuest(Request $request)
    // {
    //     $request->validate([
    //         'product_id' => 'required|exists:ec_products,id',
    //         'quantity' => 'required|integer|min:1',
    //     ]);
    
    //     $productId = $request->input('product_id');
    //     $quantity = $request->input('quantity');
    //     $sessionId = $request->session()->getId();
    
    //     $cartItem = Cart::where('session_id', $sessionId)
    //         ->where('product_id', $productId)
    //         ->first();
    
    //     if ($cartItem) {
    //         $cartItem->quantity += $quantity;
    //         $cartItem->save();
    //     } else {
    //         $cartItem = Cart::create([
    //             'session_id' => $sessionId,
    //             'product_id' => $productId,
    //             'quantity' => $quantity,
    //         ]);
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'data' => [
    //             'id' => $cartItem->id,
    //             'user_id' => $cartItem->user_id,
    //             'session_id' => $cartItem->session_id,
    //             'product_id' => $cartItem->product_id,
    //             'quantity' => $cartItem->quantity,
    //             'created_at' => $cartItem->created_at,
    //             'updated_at' => $cartItem->updated_at,
    //         ],
    //     ]);
    // }
public function addToCartGuest(Request $request)
{
    // Validate the request input
    $request->validate([
        'product_id' => 'required|exists:ec_products,id',
        'quantity' => 'required|integer|min:1',
    ]);

    $productId = $request->input('product_id');
    $quantity = $request->input('quantity');
    $userId = Auth::check() ? Auth::id() : null; // Get authenticated user ID
    $sessionId = $userId ? null : $request->session()->getId(); // Get session ID for guests

    // Query to find existing cart item
    $cartItem = Cart::where(function($query) use ($userId, $sessionId) {
        if ($userId) {
            $query->where('user_id', $userId);
        } else {
            $query->where('session_id', $sessionId);
        }
    })
    ->where('product_id', $productId)
    ->first();

    if ($cartItem) {
        // Update quantity if item already in cart
        $cartItem->quantity += $quantity;
        $cartItem->save();
    } else {
        // Create new cart item
        Cart::create([
            'user_id' => $userId,
            'session_id' => $sessionId,
            'product_id' => $productId,
            'quantity' => $quantity,
        ]);
    }

    // Fetch the current cart items
    $cartItems = Cart::where(function($query) use ($userId, $sessionId) {
        if ($userId) {
            $query->where('user_id', $userId);
        } else {
            $query->where('session_id', $sessionId);
        }
    })->with('product')
      ->get();

    return response()->json([
        'success' => true,
        'message' => 'Product added to cart',
        'cart' => $cartItems
    ]);
}





    
    public function decreaseQuantity(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:ec_products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $productId = $request->input('product_id');

        if (Auth::check()) {
            $userId = Auth::id();
            $cartItem = Cart::where('user_id', $userId)
                ->where('product_id', $productId)
                ->first();
        } else {
            $sessionId = $request->session()->getId();
            $cartItem = Cart::where('session_id', $sessionId)
                ->where('product_id', $productId)
                ->first();
        }

        if ($cartItem) {
            $cartItem->quantity -= $request->input('quantity');

            if ($cartItem->quantity <= 0) {
                $cartItem->delete();
                return response()->json(['success' => true, 'message' => 'Item removed from cart.']);
            }

            $cartItem->save();

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $cartItem->id,
                    'user_id' => $cartItem->user_id,
                    'session_id' => $cartItem->session_id,
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'created_at' => $cartItem->created_at,
                    'updated_at' => $cartItem->updated_at,
                ],
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Item not found in cart.'], 404);
    }

    public function viewCartGuest(Request $request)
    {
        $cartItems = Cart::where('session_id', $request->session()->getId())->with('product')->get();

        return response()->json([
            'success' => true,
            'data' => $cartItems,
        ]);
    }

    public function clearCartGuest(Request $request)
    {
        Cart::where('session_id', $request->session()->getId())->delete();

        return response()->json(['success' => true]);
    }
}




// class CartApiController extends Controller
// {
//     public function addToCart(Request $request)
//     {
//         $request->validate([
//             'product_id' => 'required|exists:ec_products,id',
//             'quantity' => 'required|integer|min:1',
//         ]);

//         $productId = $request->input('product_id');
//         $quantity = $request->input('quantity');

//         if (Auth::check()) {
//             // Logged-in user
//             $userId = Auth::id();
//             $cartItem = Cart::updateOrCreate(
//                 ['user_id' => $userId, 'product_id' => $productId],
//                 ['quantity' => \DB::raw("quantity + $quantity")]
//             );
//         } else {
//             // Guest user
//             $sessionId = $request->session()->getId();
//             $cartItem = Cart::updateOrCreate(
//                 ['session_id' => $sessionId, 'product_id' => $productId],
//                 ['quantity' => \DB::raw("quantity + $quantity")]
//             );
//         }

//         $cartItem = Cart::find($cartItem->id); // Get the cart item again with updated values

//         return response()->json([
//             'success' => true,
//             'data' => [
//                 'id' => $cartItem->id,
//                 'user_id' => $cartItem->user_id,
//                 'session_id' => $cartItem->session_id,
//                 'product_id' => $cartItem->product_id,
//                 'quantity' => $cartItem->quantity,
//                 'created_at' => $cartItem->created_at,
//                 'updated_at' => $cartItem->updated_at,
//             ],
//         ]);
//     }

//     public function viewCart(Request $request)
//     {
//         $cartItems = Auth::check() 
//             ? Cart::where('user_id', Auth::id())->with('product')->get() 
//             : Cart::where('session_id', $request->session()->getId())->with('product')->get();

//         return response()->json([
//             'success' => true,
//             'data' => $cartItems,
//         ]);
//     }

//     public function clearCart(Request $request)
//     {
//         if (Auth::check()) {
//             Cart::where('user_id', Auth::id())->delete();
//         } else {
//             Cart::where('session_id', $request->session()->getId())->delete();
//         }

//         return response()->json(['success' => true]);
//     }

//     // Update cart quantity method
//     public function updateCartQuantity(Request $request)
//     {
//         $request->validate([
//             'product_id' => 'required|exists:ec_products,id',
//             'quantity' => 'required|integer|min:1',
//         ]);

//         $productId = $request->input('product_id');
//         $quantity = $request->input('quantity');

//         if (Auth::check()) {
//             $userId = Auth::id();
//             $cartItem = Cart::where('user_id', $userId)->where('product_id', $productId)->first();
//         } else {
//             $sessionId = $request->session()->getId();
//             $cartItem = Cart::where('session_id', $sessionId)->where('product_id', $productId)->first();
//         }

//         if ($cartItem) {
//             $cartItem->quantity = $quantity;
//             $cartItem->save();

//             return response()->json([
//                 'success' => true,
//                 'data' => [
//                     'id' => $cartItem->id,
//                     'user_id' => $cartItem->user_id,
//                     'session_id' => $cartItem->session_id,
//                     'product_id' => $cartItem->product_id,
//                     'quantity' => $cartItem->quantity,
//                     'created_at' => $cartItem->created_at,
//                     'updated_at' => $cartItem->updated_at,
//                 ],
//             ]);
//         }

//         return response()->json(['success' => false, 'message' => 'Item not found in cart.'], 404);
//     }

//     // Additional methods for guest users
//     public function addToCartGuest(Request $request)
//     {
//         $request->validate([
//             'product_id' => 'required|exists:ec_products,id',
//             'quantity' => 'required|integer|min:1',
//         ]);
    
//         $productId = $request->input('product_id');
//         $quantity = $request->input('quantity');
//         $sessionId = $request->session()->getId();
    
//         $cartItem = Cart::where('session_id', $sessionId)
//             ->where('product_id', $productId)
//             ->first();
    
//         if ($cartItem) {
//             $cartItem->quantity += $quantity;
//             $cartItem->save();
//         } else {
//             $cartItem = Cart::create([
//                 'session_id' => $sessionId,
//                 'product_id' => $productId,
//                 'quantity' => $quantity,
//             ]);
//         }

//         return response()->json([
//             'success' => true,
//             'data' => [
//                 'id' => $cartItem->id,
//                 'user_id' => $cartItem->user_id,
//                 'session_id' => $cartItem->session_id,
//                 'product_id' => $cartItem->product_id,
//                 'quantity' => $cartItem->quantity,
//                 'created_at' => $cartItem->created_at,
//                 'updated_at' => $cartItem->updated_at,
//             ],
//         ]);
//     }
    
//     public function decreaseQuantity(Request $request)
//     {
//         $request->validate([
//             'product_id' => 'required|exists:ec_products,id',
//             'quantity' => 'required|integer|min:1',
//         ]);

//         $productId = $request->input('product_id');

//         if (Auth::check()) {
//             $userId = Auth::id();
//             $cartItem = Cart::where('user_id', $userId)
//                 ->where('product_id', $productId)
//                 ->first();
//         } else {
//             $sessionId = $request->session()->getId();
//             $cartItem = Cart::where('session_id', $sessionId)
//                 ->where('product_id', $productId)
//                 ->first();
//         }

//         if ($cartItem) {
//             $cartItem->quantity -= $request->input('quantity');

//             if ($cartItem->quantity <= 0) {
//                 $cartItem->delete();
//                 return response()->json(['success' => true, 'message' => 'Item removed from cart.']);
//             }

//             $cartItem->save();

//             return response()->json([
//                 'success' => true,
//                 'data' => [
//                     'id' => $cartItem->id,
//                     'user_id' => $cartItem->user_id,
//                     'session_id' => $cartItem->session_id,
//                     'product_id' => $cartItem->product_id,
//                     'quantity' => $cartItem->quantity,
//                     'created_at' => $cartItem->created_at,
//                     'updated_at' => $cartItem->updated_at,
//                 ],
//             ]);
//         }

//         return response()->json(['success' => false, 'message' => 'Item not found in cart.'], 404);
//     }

//     public function viewCartGuest(Request $request)
//     {
//         $cartItems = Cart::where('session_id', $request->session()->getId())->with('product')->get();

//         return response()->json([
//             'success' => true,
//             'data' => $cartItems,
//         ]);
//     }

//     public function clearCartGuest(Request $request)
//     {
//         Cart::where('session_id', $request->session()->getId())->delete();

//         return response()->json(['success' => true]);
//     }
// }
