<?php

namespace App\Jobs;

use App\Models\ReportExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GenerateCrmReportExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;

    public function __construct(public ReportExport $export) {}

    public function handle(): void
    {
        $this->export->update(['status' => 'processing']);

        try {
            $data = $this->generateData();
            $path = 'crm-exports/' . $this->export->id . '_' . now()->format('Ymd_His') . '.csv';
            $fullPath = Storage::path($path);

            $dir = dirname($fullPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $handle = fopen($fullPath, 'w');
            fputs($handle, "\xEF\xBB\xBF");

            if (!empty($data)) {
                fputcsv($handle, array_keys($data[0]));
                foreach ($data as $row) {
                    fputcsv($handle, $row);
                }
            }

            fclose($handle);

            $this->export->update([
                'status' => 'completed',
                'file_path' => $path,
            ]);
        } catch (\Exception $e) {
            $this->export->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }
    }

    private function generateData(): array
    {
        $filters = $this->export->filters ?? [];

        return match ($this->export->report_type) {
            'leads' => \App\Models\Lead::query()
                ->when($filters['campaign_id'] ?? null, fn ($q, $v) => $q->where('campaign_id', $v))
                ->get(['id', 'name', 'phone', 'email', 'status', 'created_at'])
                ->toArray(),
            'calls' => \App\Models\CallLog::query()
                ->when($filters['campaign_id'] ?? null, fn ($q, $v) => $q->where('campaign_id', $v))
                ->get(['id', 'lead_id', 'phone_number', 'status', 'duration_seconds', 'started_at'])
                ->toArray(),
            'follow-ups' => \App\Models\FollowUp::query()
                ->when($filters['campaign_id'] ?? null, fn ($q, $v) => $q->where('campaign_id', $v))
                ->get(['id', 'lead_id', 'scheduled_at', 'status', 'note'])
                ->toArray(),
            default => [],
        };
    }
}
