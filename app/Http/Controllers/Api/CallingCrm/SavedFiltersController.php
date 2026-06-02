<?php

namespace App\Http\Controllers\Api\CallingCrm;

use App\Http\Controllers\Controller;
use App\Models\SavedFilter;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SavedFiltersController extends Controller
{
    public function index(Request $request)
    {
        $filters = SavedFilter::where('user_id', auth()->id())
            ->when($request->filled('module'), fn ($q) => $q->where('module', $request->module))
            ->orderBy('name')
            ->paginate(min(max($request->integer('per_page', 25), 1), 100));

        return response()->json(['status' => true, 'data' => $filters]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'module' => ['required', Rule::in(['leads', 'reports', 'campaigns', 'calls'])],
            'name' => ['required', 'string', 'max:120'],
            'filters' => ['required', 'array'],
            'is_default' => ['sometimes', 'boolean'],
        ]);

        $filter = SavedFilter::create([
            'user_id' => auth()->id(),
            'module' => $data['module'],
            'name' => $data['name'],
            'filters' => $data['filters'],
            'is_default' => $data['is_default'] ?? false,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Filter saved successfully',
            'data' => $filter,
        ], 201);
    }

    public function update(Request $request, SavedFilter $filter)
    {
        abort_if($filter->user_id !== auth()->id(), 403);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:120'],
            'filters' => ['sometimes', 'array'],
            'is_default' => ['sometimes', 'boolean'],
        ]);

        $filter->update($data);

        return response()->json([
            'status' => true,
            'message' => 'Filter updated successfully',
            'data' => $filter->fresh(),
        ]);
    }

    public function destroy(SavedFilter $filter)
    {
        abort_if($filter->user_id !== auth()->id(), 403);

        $filter->delete();

        return response()->json(['status' => true, 'message' => 'Filter deleted successfully']);
    }
}
