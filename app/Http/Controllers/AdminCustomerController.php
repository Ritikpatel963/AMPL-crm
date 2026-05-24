<?php

namespace App\Http\Controllers;

use App\Models\AgentCustomerAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class AdminCustomerController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');

        $customers = User::query()
            ->where('role', 'customer')
            ->with('assignedAgent.agent:id,name,email,phone_number')
            ->when(in_array($status, ['pending', 'approved', 'rejected'], true), function ($query) use ($status) {
                $query->where('approval_status', $status);
            })
            ->latest()
            ->get();

        $agents = User::query()
            ->whereIn('role', ['agent', 'subadmin'])
            ->where(function ($query) {
                $query->whereNull('crm_status')->orWhere('crm_status', 'active');
            })
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone_number', 'role']);

        $counts = User::query()
            ->where('role', 'customer')
            ->selectRaw("coalesce(approval_status, 'approved') as approval_status, count(*) as total")
            ->groupBy('approval_status')
            ->pluck('total', 'approval_status');

        return view('admin_panel.customers.customer_manage', compact('customers', 'agents', 'counts', 'status'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone_number' => ['nullable', 'string', 'max:30', 'unique:users,phone_number'],
            'password' => ['required', Password::min(4)],
            'agent_id' => ['nullable', 'exists:users,id'],
        ]);

        $agentId = $data['agent_id'] ?? null;
        unset($data['agent_id']);

        $customer = User::create(array_merge($data, [
            'role' => 'customer',
            'approval_status' => 'approved',
        ]));

        if ($agentId) {
            $this->assignCustomer($customer, (int) $agentId);
        }

        return redirect()->back()->with('success', 'Customer created successfully');
    }

    public function assign(Request $request, User $customer)
    {
        $this->abortUnlessCustomer($customer);

        $data = $request->validate([
            'agent_id' => ['required', 'exists:users,id'],
        ]);

        $this->assignCustomer($customer, (int) $data['agent_id']);

        return redirect()->back()->with('success', 'Customer assigned successfully');
    }

    public function approve(User $customer)
    {
        $this->abortUnlessCustomer($customer);

        $customer->update(['approval_status' => 'approved']);

        return redirect()->back()->with('success', 'Customer approved successfully');
    }

    public function reject(User $customer)
    {
        $this->abortUnlessCustomer($customer);

        $customer->update(['approval_status' => 'rejected']);

        return redirect()->back()->with('success', 'Customer declined successfully');
    }

    public function destroy(User $customer)
    {
        $this->abortUnlessCustomer($customer);

        $customer->delete();

        return redirect()->back()->with('success', 'Customer deleted successfully');
    }

    private function assignCustomer(User $customer, int $agentId): void
    {
        User::query()
            ->whereIn('role', ['agent', 'subadmin'])
            ->whereKey($agentId)
            ->firstOrFail();

        AgentCustomerAssignment::updateOrCreate(
            ['customer_id' => $customer->id],
            ['agent_id' => $agentId]
        );
    }

    private function abortUnlessCustomer(User $customer): void
    {
        abort_unless($customer->role === 'customer', 404);
    }
}
