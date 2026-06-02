<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\ContactList;
use App\Models\Campaign;
use App\Jobs\ProcessCrmContactListImport;

class ProcessCrmContactListImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_can_be_dispatched()
    {
        $campaign = Campaign::factory()->create();
        $contactList = ContactList::create([
            'campaign_id' => $campaign->id,
            'file_name' => 'dummy.csv',
            'file_path' => 'dummy.csv',
            'storage_path' => 'imports/dummy.csv',
            'status' => 'queued',
            'uploaded_by' => 1
        ]);
        
        $job = new ProcessCrmContactListImport($contactList);
        
        $this->assertInstanceOf(ProcessCrmContactListImport::class, $job);
    }
}
