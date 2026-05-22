<?php

namespace App\Jobs;

use App\Models\ContactList;
use App\Models\ContactListRow;
use App\Models\Lead;
use App\Models\LeadSource;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProcessCrmContactListImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;
    public $maxExceptions = 1;

    public function __construct(public ContactList $contactList) {}

    public function handle(): void
    {
        $this->contactList->update(['status' => 'processing']);

        try {
            $rows = $this->parseFile();
            $sourceId = LeadSource::where('code', 'FILE_UPLOAD')->value('id');
            $campaignId = $this->contactList->campaign_id;
            $pipelineId = $this->contactList->campaign?->pipeline_id;

            $created = 0;
            $merged = 0;
            $failed = 0;

            DB::transaction(function () use ($rows, $sourceId, $campaignId, $pipelineId, &$created, &$merged, &$failed) {
                foreach ($rows as $index => $row) {
                    $rowNumber = $index + 1;
                    $phone = preg_replace('/[^0-9]+/', '', $row['phone'] ?? $row[1] ?? '');

                    try {
                        if (empty($phone)) {
                            throw new \Exception('Phone number is required');
                        }

                        if (strlen($phone) < 10) {
                            throw new \Exception("Invalid phone number: $phone");
                        }

                        $existingLead = Lead::where('campaign_id', $campaignId)
                            ->where('phone', $phone)
                            ->first();

                        if ($existingLead) {
                            $existingLead->update([
                                'name' => $row['name'] ?? $row[0] ?? $existingLead->name,
                                'email' => $row['email'] ?? $row[2] ?? $existingLead->email,
                            ]);
                            $status = 'merged';
                            $leadId = $existingLead->id;
                            $merged++;
                        } else {
                            $lead = Lead::create([
                                'campaign_id' => $campaignId,
                                'pipeline_id' => $pipelineId,
                                'source_id' => $sourceId,
                                'contact_list_id' => $this->contactList->id,
                                'name' => $row['name'] ?? $row[0] ?? null,
                                'phone' => $phone,
                                'email' => $row['email'] ?? $row[2] ?? null,
                                'status' => 'uncontacted',
                            ]);
                            $status = 'created';
                            $leadId = $lead->id;
                            $created++;
                        }

                        ContactListRow::create([
                            'contact_list_id' => $this->contactList->id,
                            'lead_id' => $leadId,
                            'row_number' => $rowNumber,
                            'raw_payload' => $row,
                            'status' => $status,
                        ]);
                    } catch (\Exception $e) {
                        $failed++;
                        ContactListRow::create([
                            'contact_list_id' => $this->contactList->id,
                            'row_number' => $rowNumber,
                            'raw_payload' => $row,
                            'status' => 'failed',
                            'failure_reason' => $e->getMessage(),
                        ]);
                    }
                }
            });

            $overallStatus = $failed === 0 ? 'completed' : ($created + $merged > 0 ? 'partially_failed' : 'failed');

            $this->contactList->update([
                'status' => $overallStatus,
                'total_rows' => count($rows),
                'created_rows' => $created,
                'merged_rows' => $merged,
                'failed_rows' => $failed,
                'processed_at' => now(),
            ]);
        } catch (\Exception $e) {
            $this->contactList->update(['status' => 'failed']);
            Log::error('CRM import processing failed', [
                'contact_list_id' => $this->contactList->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function parseFile(): array
    {
        $path = Storage::path($this->contactList->storage_path);

        if (!file_exists($path)) {
            throw new \Exception('Import file not found: ' . $this->contactList->storage_path);
        }

        $mime = $this->contactList->mime_type;
        $extension = pathinfo($this->contactList->file_name, PATHINFO_EXTENSION);

        if (in_array($extension, ['csv', 'txt']) || $mime === 'text/csv') {
            return $this->parseCsv($path);
        }

        return $this->parseExcel($path);
    }

    private function parseCsv(string $path): array
    {
        $rows = [];
        if (($handle = fopen($path, 'r')) !== false) {
            $headers = fgetcsv($handle);
            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) === count($headers)) {
                    $rows[] = array_combine($headers, $row);
                } else {
                    $rows[] = $row;
                }
            }
            fclose($handle);
        }
        return $rows;
    }

    private function parseExcel(string $path): array
    {
        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray();

        if (empty($data)) {
            return [];
        }

        $headers = array_shift($data);
        $rows = [];

        foreach ($data as $row) {
            if (count($row) === count($headers)) {
                $rows[] = array_combine($headers, $row);
            } else {
                $rows[] = $row;
            }
        }

        return $rows;
    }
}
