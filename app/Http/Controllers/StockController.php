<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Stock;

class StockController extends Controller
{
    public function index()
    {
       $products = Product::all(); // for dropdown
       $stocks = Stock::with('product')->latest()->get(); // for stock history
        return view('admin_panel.stock.add-update-stock', compact('stocks', 'products'));
    }

    public function store(Request $request)
{
    $request->validate([
        'product_id' => 'required|exists:products,id',
        'quantity' => 'required|integer|min:1',
        'note' => 'nullable|string',
    ]);

    // Step 1: Create a stock record (for history)
    Stock::create([
        'product_id' => $request->product_id,
        'type' => 'purchase', // since we are adding stock manually
        'quantity' => $request->quantity,
        'note' => $request->note,
    ]);

    // Step 2: Update product's total stock
    $product = Product::find($request->product_id);
    $product->increment('stock_quantity', $request->quantity);

    return redirect()->back()->with('success', 'Stock added successfully!');
}
}
