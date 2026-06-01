<?php

namespace App\Http\Controllers;

use App\Models\ProductAttribute;
use Illuminate\Http\Request;

class ProductAttributeController extends Controller
{
    public function index()
    {
        $attributes = ProductAttribute::latest()->paginate(15);

        return view('admin_panel.product_attributes.index', compact('attributes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:product_attributes,name'],
            'values' => ['required', 'string'],
            'status' => ['nullable'],
        ]);

        ProductAttribute::create([
            'name' => $data['name'],
            'values' => $this->parseValues($data['values']),
            'status' => $request->has('status'),
        ]);

        return redirect()->route('admin_panel.admin.product_attributes.index')
            ->with('success', 'Attribute created successfully.');
    }

    public function update(Request $request, ProductAttribute $productAttribute)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:product_attributes,name,' . $productAttribute->id],
            'values' => ['required', 'string'],
            'status' => ['nullable'],
        ]);

        $productAttribute->update([
            'name' => $data['name'],
            'values' => $this->parseValues($data['values']),
            'status' => $request->has('status'),
        ]);

        return redirect()->route('admin_panel.admin.product_attributes.index')
            ->with('success', 'Attribute updated successfully.');
    }

    public function destroy(ProductAttribute $productAttribute)
    {
        $productAttribute->delete();

        return redirect()->route('admin_panel.admin.product_attributes.index')
            ->with('success', 'Attribute deleted successfully.');
    }

    private function parseValues(string $values): array
    {
        return collect(preg_split('/[\r\n,]+/', $values))
            ->map(fn ($value) => trim($value))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
