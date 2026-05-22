<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Collection;

class BulkActions extends Component
{
    public array $selectedIds = [];
    public bool $showDropdown = false;
    public string $actionType = '';
    public array $availableUsers = [];
    public array $availableCampaigns = [];

    public function mount()
    {
        $this->availableUsers = \Illuminate\Support\Facades\Cache::remember('crm:available_users', 300, function () {
            return \App\Models\User::pluck('name', 'id')->toArray();
        });
        $this->availableCampaigns = \Illuminate\Support\Facades\Cache::remember('crm:available_categories', 300, function () {
            return \App\Models\Category::pluck('name', 'id')->toArray();
        });
    }

    public function toggleSelection($leadId)
    {
        if (in_array($leadId, $this->selectedIds)) {
            $this->selectedIds = array_filter(
                $this->selectedIds,
                fn($id) => $id !== $leadId
            );
        } else {
            $this->selectedIds[] = $leadId;
        }
    }

    public function selectAll($allIds)
    {
        $this->selectedIds = !empty($this->selectedIds) && count($this->selectedIds) === count($allIds)
            ? []
            : $allIds;
    }

    public function toggleDropdown()
    {
        if (empty($this->selectedIds)) {
            session()->flash('error', 'Please select at least one lead');
            return;
        }
        $this->showDropdown = !$this->showDropdown;
    }

    public function selectAction($action)
    {
        if (empty($this->selectedIds)) {
            session()->flash('error', 'Please select at least one lead');
            return;
        }

        $this->actionType = $action;

        match ($action) {
            'delete' => $this->deleteLeads(),
            'update' => $this->showUpdateModal(),
            'move' => $this->showMoveModal(),
            'copy' => $this->showCopyModal(),
            'close' => $this->closeLeads(),
            default => null,
        };

        $this->showDropdown = false;
    }

    public function deleteLeads()
    {
        try {
            \App\Models\Message::whereIn('id', $this->selectedIds)->delete();
            $this->selectedIds = [];
            session()->flash('success', 'Leads deleted successfully');
        } catch (\Exception $e) {
            session()->flash('error', 'Error deleting leads: ' . $e->getMessage());
        }
    }

    public function updateLeads($userId, $stage, $tag)
    {
        try {
            \App\Models\Message::whereIn('id', $this->selectedIds)->update([
                'user_assigned' => $userId,
                'lead_stage' => $stage,
                'tag' => $tag,
            ]);
            $this->selectedIds = [];
            session()->flash('success', 'Leads updated successfully');
        } catch (\Exception $e) {
            session()->flash('error', 'Error updating leads: ' . $e->getMessage());
        }
    }

    public function moveLeads($campaignId)
    {
        try {
            \App\Models\Message::whereIn('id', $this->selectedIds)->update([
                'campaign_id' => $campaignId,
            ]);
            $this->selectedIds = [];
            session()->flash('success', 'Leads moved successfully');
        } catch (\Exception $e) {
            session()->flash('error', 'Error moving leads: ' . $e->getMessage());
        }
    }

    public function copyLeads($campaignId)
    {
        try {
            $leads = \App\Models\Message::whereIn('id', $this->selectedIds)->get();

            foreach ($leads as $lead) {
                $lead->replicate()->update([
                    'campaign_id' => $campaignId,
                    'created_at' => now(),
                ])->save();
            }

            session()->flash('success', 'Leads copied successfully');
        } catch (\Exception $e) {
            session()->flash('error', 'Error copying leads: ' . $e->getMessage());
        }
    }

    public function closeLeads()
    {
        try {
            \App\Models\Message::whereIn('id', $this->selectedIds)->update([
                'lead_status' => 'Closed',
            ]);
            $this->selectedIds = [];
            session()->flash('success', 'Leads closed successfully');
        } catch (\Exception $e) {
            session()->flash('error', 'Error closing leads: ' . $e->getMessage());
        }
    }

    public function showUpdateModal()
    {
        $this->dispatch('openUpdateModal');
    }

    public function showMoveModal()
    {
        $this->dispatch('openMoveModal');
    }

    public function showCopyModal()
    {
        $this->dispatch('openCopyModal');
    }

    public function render()
    {
        return view('livewire.bulk-actions', [
            'selectedCount' => count($this->selectedIds),
            'showDropdown' => $this->showDropdown,
        ]);
    }
}
