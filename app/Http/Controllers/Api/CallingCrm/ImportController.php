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
            ->when($request->filled('campaign_id'), fn($query) => $query->where('campaign_id', $request->campaign_id))
            ->when($request->filled('status'), fn($query) => $query->where('status', $request->status))
            ->latest()
            ->paginate(min(max($request->integer('per_page', 25), 1), 100));

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

        $userId = auth()->id();

        // Handle case where an Admin is uploading but the table expects a User ID
        if (!\App\Models\User::where('id', $userId)->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized: Only CRM users (agents/subadmins) can upload contact lists.',
            ], 403);
        }

        $import = ContactList::create([
            'campaign_id' => $campaign->id,
            'uploaded_by' => $userId,
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
            ->when($request->filled('status'), fn($query) => $query->where('status', $request->status))
            ->when($request->filled('search'), fn($query) => $query->where('raw_payload', 'like', '%' . $request->search . '%'))
            ->orderBy('row_number')
            ->paginate(min(max($request->integer('per_page', 50), 1), 100));

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

    public function preview(Request $request, Campaign $campaign)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,xls,xlsx', 'max:3072'],
        ]);

        $file = $request->file('file');
        $tempPath = $file->getPathname();
        $extension = strtolower($file->getClientOriginalExtension());

        try {
            if (in_array($extension, ['csv', 'txt'])) {
                $parsed = $this->previewCsv($tempPath);
            } else {
                $parsed = $this->previewExcel($tempPath);
            }

            $headers = $parsed['headers'] ?? [];
            $sampleRows = $parsed['rows'] ?? [];
            $suggestedMapping = $this->suggestMapping($headers);

            return response()->json([
                'status' => true,
                'data' => [
                    'headers' => $headers,
                    'sample_rows' => $sampleRows,
                    'suggested_mapping' => $suggestedMapping,
                    'total_rows_hint' => $parsed['total'] ?? count($sampleRows),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Could not parse the file: ' . $e->getMessage(),
            ], 422);
        }
    }

    private function previewCsv(string $path, int $limit = 5): array
    {
        $headers = [];
        $rows = [];
        $total = 0;

        if (($handle = fopen($path, 'r')) !== false) {
            $headers = fgetcsv($handle) ?: [];
            while (($row = fgetcsv($handle)) !== false) {
                $total++;
                if (count($rows) < $limit) {
                    $rows[] = count($row) === count($headers) ? array_combine($headers, $row) : $row;
                }
            }
            fclose($handle);
        }

        return ['headers' => $headers, 'rows' => $rows, 'total' => $total];
    }

    private function previewExcel(string $path, int $limit = 5): array
    {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray();

        if (empty($data)) {
            return ['headers' => [], 'rows' => [], 'total' => 0];
        }

        $headers = array_shift($data);
        $total = count($data);
        $rows = [];

        foreach (array_slice($data, 0, $limit) as $row) {
            $rows[] = count($row) === count($headers) ? array_combine($headers, $row) : $row;
        }

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return ['headers' => $headers, 'rows' => $rows, 'total' => $total];
    }

    private function suggestMapping(array $headers): array
    {
        $patterns = [
            'name' => ['name', 'full_name', 'fullname', 'contact_name', 'contactname', 'customer_name', 'lead_name'],
            'phone' => ['phone', 'mobile', 'number', 'contact_number', 'phone_number', 'mobile_number', 'mobile_no', 'phone_no', 'tel', 'telephone', 'cell'],
            'email' => ['email', 'mail', 'email_address', 'emailid', 'email_id', 'e_mail'],
            'company_name' => ['company', 'company_name', 'companyname', 'organization', 'org', 'firm'],
            'address' => ['address', 'address_line_1', 'address1', 'street', 'street_address'],
            'city' => ['city', 'town', 'town_city', 'town/city'],
            'state' => ['state', 'province', 'region'],
            'pincode' => ['pincode', 'zip', 'zipcode', 'zip_code', 'postal_code', 'postalcode', 'pin'],
            'gst' => ['gst', 'gstin', 'gst_number', 'gst_no', 'tax_id'],
        ];

        $mapping = [];

        foreach ($headers as $header) {
            $normalized = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '_', $header), '_'));
            $matched = 'skip';

            foreach ($patterns as $field => $keywords) {
                if (in_array($normalized, $keywords, true)) {
                    $matched = $field;
                    break;
                }
            }

            $mapping[$header] = $matched;
        }

        return $mapping;
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

    public function exportFailedRows(Campaign $campaign, ContactList $import)
    {
        if ($import->campaign_id !== $campaign->id) {
            abort(404);
        }

        $failedRows = $import->rows()->where('status', 'failed')->get();

        if ($failedRows->isEmpty()) {
            return response()->json(['message' => 'No failed rows to export.'], 404);
        }

        $filename = 'failed_rows_import_' . $import->id . '_' . time() . '.csv';
        $path = storage_path('app/temp/' . $filename);

        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $handle = fopen($path, 'w');

        // Extract headers from the first failed row's raw payload
        $firstRowPayload = $failedRows->first()->raw_payload;
        $headers = is_array($firstRowPayload) ? array_keys($firstRowPayload) : [];
        $headers[] = 'Failure Reason'; // Append the failure reason column
        fputcsv($handle, $headers);

        foreach ($failedRows as $row) {
            $rowData = is_array($row->raw_payload) ? array_values($row->raw_payload) : [];
            $rowData[] = $row->failure_reason;
            fputcsv($handle, $rowData);
        }
        fclose($handle);

        return response()->download($path, $filename, ['Content-Type' => 'text/csv'])->deleteFileAfterSend(true);
    }
}
