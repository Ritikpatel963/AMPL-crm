<?php

namespace App\Jobs;

use App\Models\ContactList;
use App\Models\ContactListRow;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Services\CallingCrm\LeadAssignmentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\LazyCollection;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProcessCrmContactListImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;
    public $maxExceptions = 1;

    public function __construct(public ContactList $contactList) {}

    public function handle(LeadAssignmentService $assignmentService): void
    {
        $this->contactList->update(['status' => 'processing']);

        try {
            $rowsLazy = $this->parseFile();
            $sourceId = LeadSource::where('code', 'FILE_UPLOAD')->value('id');
            $campaignId = $this->contactList->campaign_id;
            $pipelineId = $this->contactList->campaign?->pipeline_id;
            $mapping = $this->contactList->mapping;

            $created = 0;
            $merged = 0;
            $failed = 0;
            $totalRows = 0;
            
            $seenPhonesGlobal = [];

            $rowsLazy->chunk(500)->each(function ($chunk) use ($sourceId, $campaignId, $pipelineId, $mapping, $assignmentService, &$created, &$merged, &$failed, &$totalRows, &$seenPhonesGlobal) {
                $chunkValidData = [];
                $rowsToInsert = [];
                $phonesToQuery = [];
                
                foreach ($chunk as $item) {
                    $index = $item['index'];
                    $row = $item['row'];
                    $rowNumber = $index + 1;
                    $totalRows++;
                    
                    $rawPhone = $this->resolveMapped($row, $mapping, 'phone', 1);
                    $phone = preg_replace('/[^0-9]+/', '', $rawPhone);
                    
                    try {
                        if (empty($phone)) {
                            throw new \Exception('Phone number is required');
                        }
                        if (strlen($phone) < 10) {
                            throw new \Exception("Invalid phone format: {$rawPhone}");
                        }
                        if (isset($seenPhonesGlobal[$phone]) || isset($phonesToQuery[$phone])) {
                            throw new \Exception("Duplicate number in file: {$phone}");
                        }
                        
                        $phonesToQuery[$phone] = true;
                        
                        $name = $this->resolveMapped($row, $mapping, 'name', 0);
                        $email = $this->resolveMapped($row, $mapping, 'email', 2);
                        
                        $locationStr = $this->resolveMapped($row, $mapping, 'location', -1);
                        $locationId = null;
                        if (!empty($locationStr)) {
                            $location = \App\Models\Location::firstOrCreate(
                                ['name' => $locationStr],
                                ['is_active' => true]
                            );
                            $locationId = $location->id;
                        }

                        $metadata = [];
                        foreach ($row as $header => $val) {
                            $mappedKey = $mapping[$header] ?? null;
                            if ($mappedKey && $mappedKey !== 'skip') {
                                $metadata[$mappedKey] = trim((string)$val);
                            }
                            $metadata[$header] = trim((string)$val);
                        }
                        
                        $chunkValidData[] = [
                            'rowNumber' => $rowNumber,
                            'row' => $row,
                            'phone' => $phone,
                            'name' => $name,
                            'email' => $email,
                            'location_id' => $locationId,
                            'metadata' => $metadata,
                        ];
                    } catch (\Exception $e) {
                        $failed++;
                        $rowsToInsert[] = [
                            'contact_list_id' => $this->contactList->id,
                            'lead_id' => null,
                            'row_number' => $rowNumber,
                            'raw_payload' => is_array($row) ? json_encode($row) : json_encode([$row]),
                            'status' => 'failed',
                            'failure_reason' => $e->getMessage(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
                
                $existingPhones = [];
                if (!empty($phonesToQuery)) {
                    $existingPhones = Lead::where('campaign_id', $campaignId)
                        ->whereIn('phone', array_keys($phonesToQuery))
                        ->pluck('phone')
                        ->toArray();
                }
                $existingPhones = array_flip($existingPhones);
                
                $leadsToInsert = [];
                $validRowsMapping = [];
                
                DB::transaction(function () use ($chunkValidData, $existingPhones, $sourceId, $campaignId, $pipelineId, &$created, &$failed, &$seenPhonesGlobal, &$leadsToInsert, &$rowsToInsert, &$validRowsMapping) {
                    foreach ($chunkValidData as $data) {
                        $phone = $data['phone'];
                        if (isset($existingPhones[$phone])) {
                            $failed++;
                            $rowsToInsert[] = [
                                'contact_list_id' => $this->contactList->id,
                                'lead_id' => null,
                                'row_number' => $data['rowNumber'],
                                'raw_payload' => is_array($data['row']) ? json_encode($data['row']) : json_encode([$data['row']]),
                                'status' => 'failed',
                                'failure_reason' => "Duplicate number in campaign: {$phone}",
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        } else {
                            $seenPhonesGlobal[$phone] = true;
                            
                            $leadsToInsert[] = [
                                'campaign_id' => $campaignId,
                                'pipeline_id' => $pipelineId,
                                'source_id' => $sourceId,
                                'contact_list_id' => $this->contactList->id,
                                'name' => $data['name'] ?: null,
                                'phone' => $phone,
                                'email' => $data['email'] ?: null,
                                'location_id' => $data['location_id'] ?: null,
                                'status' => 'uncontacted',
                                'metadata' => is_array($data['metadata']) ? json_encode($data['metadata']) : json_encode([]),
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                            
                            $validRowsMapping[$phone] = [
                                'rowNumber' => $data['rowNumber'],
                                'row' => $data['row']
                            ];
                        }
                    }
                    
                    if (!empty($leadsToInsert)) {
                        Lead::insert($leadsToInsert);
                    }
                    if (!empty($rowsToInsert)) {
                        ContactListRow::insert($rowsToInsert);
                    }
                });
                
                if (!empty($leadsToInsert)) {
                    $phonesInserted = array_column($leadsToInsert, 'phone');
                    $insertedLeads = Lead::where('campaign_id', $campaignId)->whereIn('phone', $phonesInserted)->get();
                    
                    $successRowsToInsert = [];
                    foreach ($insertedLeads as $lead) {
                        $assignmentService->assignLead($lead);
                        $created++;
                        
                        $rowData = $validRowsMapping[$lead->phone];
                        
                        $successRowsToInsert[] = [
                            'contact_list_id' => $this->contactList->id,
                            'lead_id' => $lead->id,
                            'row_number' => $rowData['rowNumber'],
                            'raw_payload' => is_array($rowData['row']) ? json_encode($rowData['row']) : json_encode([$rowData['row']]),
                            'status' => 'created',
                            'failure_reason' => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                    
                    if (!empty($successRowsToInsert)) {
                        ContactListRow::insert($successRowsToInsert);
                    }
                }
            });

            $overallStatus = $failed === 0 ? 'completed' : ($created + $merged > 0 ? 'partially_failed' : 'failed');

            $this->contactList->update([
                'status' => $overallStatus,
                'total_rows' => $totalRows,
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

    private function resolveMapped(array $row, ?array $mapping, string $field, int $fallbackIndex): string
    {
        if ($mapping) {
            foreach ($mapping as $header => $mappedField) {
                if ($mappedField === $field && array_key_exists($header, $row)) {
                    return trim((string) ($row[$header] ?? ''));
                }
            }
            return '';
        }

        return trim((string) ($row[$field] ?? $row[$fallbackIndex] ?? ''));
    }

    private function parseFile(): LazyCollection
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

    private function parseCsv(string $path): LazyCollection
    {
        return LazyCollection::make(function () use ($path) {
            $handle = fopen($path, 'r');
            if ($handle === false) return;
            
            $headers = fgetcsv($handle);
            $index = 0;
            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) === count($headers)) {
                    yield ['index' => $index, 'row' => array_combine($headers, $row)];
                } else {
                    yield ['index' => $index, 'row' => $row];
                }
                $index++;
            }
            fclose($handle);
        });
    }

    private function parseExcel(string $path): LazyCollection
    {
        return LazyCollection::make(function () use ($path) {
            $spreadsheet = IOFactory::load($path);
            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray();
            
            if (empty($data)) return;
            
            $headers = array_shift($data);
            $index = 0;
            
            foreach ($data as $row) {
                if (count($row) === count($headers)) {
                    yield ['index' => $index, 'row' => array_combine($headers, $row)];
                } else {
                    yield ['index' => $index, 'row' => $row];
                }
                $index++;
            }
        });
    }
}
