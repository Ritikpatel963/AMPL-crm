<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;

class OrderController extends Controller
{

     public function index()
    {
        // Fetch all orders (you can modify to only fetch logged-in user orders if needed)
        $orders = Order::with('items.product')->latest()->get();
        return view('admin_panel.orders.index', compact('orders'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'name' => 'required|string',
            'phone' => 'required',
            'address' => 'required',
        ]);

        $product = Product::findOrFail($request->product_id);

        if ($product->stock_quantity < $request->quantity) {
            return back()->with('error', 'Insufficient stock for this product!');
        }

        // Deduct stock
        $product->decrement('stock_quantity', $request->quantity);

        // Create Order
        $order = Order::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'total_amount' => $product->sale_price * $request->quantity,
        ]);

        // Create Order Item
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => $request->quantity,
            'price' => $product->sale_price,
        ]);

        return redirect()->route('shop.index')->with('success', 'Order placed successfully!');
    }
    public function updateStatus(Request $request, $id)
{
    $request->validate([
        'status' => 'required|string'
    ]);

    $order = \App\Models\Order::findOrFail($id);
    $order->status = $request->status;
    $order->save();

    return response()->json(['success' => true, 'message' => 'Order status updated successfully.']);
}
}

