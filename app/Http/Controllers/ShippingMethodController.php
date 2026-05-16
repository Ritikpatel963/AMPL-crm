<?php

namespace App\Http\Controllers;

use App\Models\ShippingMethod;
use Illuminate\Http\Request;

class ShippingMethodController extends Controller
{
    public function index()
    {
        $shipping_methods = ShippingMethod::latest()->get();
        return view('admin_panel.shipping.index', compact('shipping_methods'));

    }

    public function store(Request $request)
    {
        $request->validate([
            'method_name' => 'required|string|max:255',
            'cost' => 'required|numeric|min:0',
            'delivery_time' => 'required|string|max:255',
            'status' => 'required|in:Active,Inactive'
        ]);

        ShippingMethod::create([
            'method_name' => $request->method_name,
            'cost' => $request->cost,
            'delivery_time' => $request->delivery_time,
            'status' => $request->status
        ]);

        return redirect()->back()->with('success', 'Shipping Method Added Successfully');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'method_name' => 'required|string|max:255',
            'cost' => 'required|numeric|min:0',
            'delivery_time' => 'required|string|max:255',
            'status' => 'required|in:Active,Inactive'
        ]);

        $shipping = ShippingMethod::findOrFail($id);

        $shipping->update([
            'method_name' => $request->method_name,
            'cost' => $request->cost,
            'delivery_time' => $request->delivery_time,
            'status' => $request->status
        ]);

        return response()->json(['success' => true]);
    }

     public function destroy($id)
    {
        ShippingMethod::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}