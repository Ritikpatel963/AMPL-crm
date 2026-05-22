<?php

namespace App\Jobs;

use App\Models\ReportExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ExpireCrmReportExports implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        ReportExport::where('status', 'completed')
            ->where('expires_at', '<', now())
            ->chunkById(50, function ($exports) {
                foreach ($exports as $export) {
                    if ($export->file_path && Storage::exists($export->file_path)) {
                        Storage::delete($export->file_path);
                    }
                    $export->delete();
                }
            });
    }
}
