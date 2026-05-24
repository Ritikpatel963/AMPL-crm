<?php

namespace App\Http\Controllers\Api\CallingCrm;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessCrmContactListImport;
use App\Models\Campaign;
use App\Models\ContactList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImportController extends Controller
{
    public function index(Request $request)
    {
        $imports = ContactList::with(['campaign', 'uploadedBy'])
            ->when($request->filled('campaign_id'), fn ($query) => $query->where('campaign_id', $request->campaign_id))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->latest()
            ->paginate($request->integer('per_page', 25));

        return response()->json(['status' => true, 'data' => $imports]);
    }

    public function store(Request $request, Campaign $campaign)
    {
        $data = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,xls,xlsx', 'max:3072'],
            'sheet_name' => ['nullable', 'string', 'max:160'],
            'mapping' => ['nullable', 'array'],
        ]);

        $file = $data['file'];
        $path = $file->store('calling-crm/imports');

        $import = ContactList::create([
            'campaign_id' => $campaign->id,
            'uploaded_by' => auth()->id(),
            'file_name' => $file->getClientOriginalName(),
            'sheet_name' => $data['sheet_name'] ?? null,
            'storage_path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'status' => 'queued',
            'mapping' => $data['mapping'] ?? null,
        ]);

        ProcessCrmContactListImport::dispatch($import);

        return response()->json([
            'status' => true,
            'message' => 'Import uploaded successfully',
            'data' => $import,
        ], 201);
    }

    public function show(ContactList $import)
    {
        return response()->json([
            'status' => true,
            'data' => $import->load(['campaign', 'uploadedBy', 'rows.lead']),
        ]);
    }

    public function rows(Request $request, ContactList $import)
    {
        $rows = $import->rows()
            ->with('lead')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('search'), fn ($query) => $query->where('raw_payload', 'like', '%' . $request->search . '%'))
            ->orderBy('row_number')
            ->paginate($request->integer('per_page', 50));

        return response()->json(['status' => true, 'data' => $rows]);
    }

    public function destroy(ContactList $import)
    {
        if ($import->storage_path) {
            Storage::delete($import->storage_path);
        }

        $import->delete();

        return response()->json(['status' => true, 'message' => 'Import deleted successfully']);
    }

    public function sample()
    {
        $headers = ['Name', 'Phone', 'Email', 'Company Name', 'Address', 'City', 'State', 'Pincode', 'GST'];
        $sampleData = [
            ['John Doe', '9876543210', 'john@example.com', 'ABC Corp', '123 Street', 'Mumbai', 'Maharashtra', '400001', '27ABCDE1234F1Z5'],
        ];

        $filename = 'import_sample.csv';
        $path = tempnam(sys_get_temp_dir(), 'crm_import');
        $handle = fopen($path, 'w');
        fputcsv($handle, $headers);
        foreach ($sampleData as $row) {
            fputcsv($handle, $row);
        }
        fclose($handle);

        return response()->download($path, $filename, ['Content-Type' => 'text/csv'])->deleteFileAfterSend(true);
    }
}
