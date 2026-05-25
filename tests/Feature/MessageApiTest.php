<?php

namespace Tests\Feature;

use App\Models\AgentCustomerAssignment;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MessageApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_send_message_to_assigned_agent_without_receiver_id(): void
    {
        config(['broadcasting.default' => 'null']);

        $agent = User::factory()->create(['role' => 'agent']);
        $customer = User::factory()->create([
            'role' => 'customer',
            'approval_status' => 'approved',
        ]);

        AgentCustomerAssignment::create([
            'agent_id' => $agent->id,
            'customer_id' => $customer->id,
        ]);

        Sanctum::actingAs($customer);

        $response = $this->postJson('/api/messages/send', [
            'message' => 'Hello from customer',
        ]);

        $response->assertCreated()
            ->assertJsonPath('status', true)
            ->assertJsonPath('message.sender_id', $customer->id)
            ->assertJsonPath('message.receiver_id', $agent->id);

        $this->assertDatabaseHas('messages', [
            'sender_id' => $customer->id,
            'receiver_id' => $agent->id,
            'message' => 'Hello from customer',
            'type' => 'text',
        ]);
    }

    public function test_unassigned_customer_message_is_rejected_without_creating_message(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'approval_status' => 'approved',
        ]);

        Sanctum::actingAs($customer);

        $response = $this->postJson('/api/messages/send', [
            'message' => 'Hello',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('status', false);

        $this->assertSame(0, Message::count());
    }

    public function test_agent_can_send_message_to_assigned_customer(): void
    {
        config(['broadcasting.default' => 'null']);

        $agent = User::factory()->create(['role' => 'agent']);
        $customer = User::factory()->create([
            'role' => 'customer',
            'approval_status' => 'approved',
        ]);

        AgentCustomerAssignment::create([
            'agent_id' => $agent->id,
            'customer_id' => $customer->id,
        ]);

        Sanctum::actingAs($agent);

        $response = $this->postJson('/api/messages/send', [
            'receiver_id' => $customer->id,
            'message' => 'Hello from agent',
        ]);

        $response->assertCreated()
            ->assertJsonPath('status', true)
            ->assertJsonPath('message.sender_id', $agent->id)
            ->assertJsonPath('message.receiver_id', $customer->id);

        $this->assertDatabaseHas('messages', [
            'sender_id' => $agent->id,
            'receiver_id' => $customer->id,
            'message' => 'Hello from agent',
            'type' => 'text',
        ]);
    }

    public function test_mobile_client_can_authorize_own_private_chat_channel(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'approval_status' => 'approved',
        ]);

        Sanctum::actingAs($customer);

        $response = $this->postJson('/api/broadcasting/auth', [
            'socket_id' => '1234.5678',
            'channel_name' => 'private-chat-channel.' . $customer->id,
        ]);

        $response->assertOk()
            ->assertJsonStructure(['auth']);
    }

    public function test_customer_current_conversation_loads_messages_after_screen_reopen(): void
    {
        config(['broadcasting.default' => 'null']);

        $agent = User::factory()->create(['role' => 'agent']);
        $customer = User::factory()->create([
            'role' => 'customer',
            'approval_status' => 'approved',
        ]);

        AgentCustomerAssignment::create([
            'agent_id' => $agent->id,
            'customer_id' => $customer->id,
        ]);

        Message::create([
            'sender_id' => $customer->id,
            'receiver_id' => $agent->id,
            'message' => 'Already saved message',
            'type' => 'text',
        ]);

        Sanctum::actingAs($customer);

        $response = $this->getJson('/api/messages');

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('chat_user_id', $agent->id)
            ->assertJsonPath('messages.0.message', 'Already saved message')
            ->assertJsonPath('messages.0.sender.id', $customer->id)
            ->assertJsonPath('messages.0.receiver.id', $agent->id);
    }

    public function test_reassigning_customer_moves_chat_access_to_new_agent_only(): void
    {
        config(['broadcasting.default' => 'null']);

        $oldAgent = User::factory()->create(['role' => 'agent']);
        $newAgent = User::factory()->create(['role' => 'agent']);
        $customer = User::factory()->create([
            'role' => 'customer',
            'approval_status' => 'approved',
        ]);

        AgentCustomerAssignment::create([
            'agent_id' => $oldAgent->id,
            'customer_id' => $customer->id,
        ]);

        AgentCustomerAssignment::create([
            'agent_id' => $newAgent->id,
            'customer_id' => $customer->id,
        ]);

        Sanctum::actingAs($oldAgent);

        $this->postJson('/api/messages/send', [
            'receiver_id' => $customer->id,
            'message' => 'Old agent should fail',
        ])->assertForbidden();

        Sanctum::actingAs($newAgent);

        $this->postJson('/api/messages/send', [
            'receiver_id' => $customer->id,
            'message' => 'New agent should work',
        ])->assertCreated();

        Sanctum::actingAs($customer);

        $this->getJson('/api/customer/agent')
            ->assertOk()
            ->assertJsonPath('agent_id', $newAgent->id);
    }

    public function test_old_agent_does_not_receive_reassigned_customer_in_chat_list(): void
    {
        $oldAgent = User::factory()->create(['role' => 'agent']);
        $newAgent = User::factory()->create(['role' => 'agent']);
        $customer = User::factory()->create([
            'role' => 'customer',
            'approval_status' => 'approved',
        ]);

        AgentCustomerAssignment::create([
            'agent_id' => $oldAgent->id,
            'customer_id' => $customer->id,
        ]);

        AgentCustomerAssignment::create([
            'agent_id' => $newAgent->id,
            'customer_id' => $customer->id,
        ]);

        Sanctum::actingAs($oldAgent);

        $this->getJson('/api/agent/customers')
            ->assertOk()
            ->assertJsonPath('customers', []);

        Sanctum::actingAs($newAgent);

        $this->getJson('/api/agent/customers')
            ->assertOk()
            ->assertJsonPath('customers.0.id', $customer->id);
    }

    public function test_reassigned_customer_old_chat_history_is_visible_to_new_agent_and_customer(): void
    {
        config(['broadcasting.default' => 'null']);

        $oldAgent = User::factory()->create(['role' => 'agent']);
        $newAgent = User::factory()->create(['role' => 'agent']);
        $customer = User::factory()->create([
            'role' => 'customer',
            'approval_status' => 'approved',
        ]);

        AgentCustomerAssignment::create([
            'agent_id' => $oldAgent->id,
            'customer_id' => $customer->id,
        ]);

        Message::create([
            'sender_id' => $customer->id,
            'receiver_id' => $oldAgent->id,
            'message' => 'Old customer message',
            'type' => 'text',
        ]);

        Message::create([
            'sender_id' => $oldAgent->id,
            'receiver_id' => $customer->id,
            'message' => 'Old agent reply',
            'type' => 'text',
        ]);

        AgentCustomerAssignment::create([
            'agent_id' => $newAgent->id,
            'customer_id' => $customer->id,
        ]);

        Sanctum::actingAs($newAgent);

        $this->getJson('/api/messages/' . $customer->id)
            ->assertOk()
            ->assertJsonPath('messages.0.message', 'Old customer message')
            ->assertJsonPath('messages.1.message', 'Old agent reply');

        Sanctum::actingAs($customer);

        $this->getJson('/api/messages')
            ->assertOk()
            ->assertJsonPath('chat_user_id', $newAgent->id)
            ->assertJsonPath('messages.0.message', 'Old customer message')
            ->assertJsonPath('messages.1.message', 'Old agent reply');
    }

    public function test_new_agent_customer_list_uses_old_history_after_reassignment(): void
    {
        $oldAgent = User::factory()->create(['role' => 'agent']);
        $newAgent = User::factory()->create(['role' => 'agent']);
        $customer = User::factory()->create([
            'role' => 'customer',
            'approval_status' => 'approved',
        ]);

        AgentCustomerAssignment::create([
            'agent_id' => $oldAgent->id,
            'customer_id' => $customer->id,
        ]);

        Message::create([
            'sender_id' => $oldAgent->id,
            'receiver_id' => $customer->id,
            'message' => 'Previous history stays',
            'type' => 'text',
        ]);

        AgentCustomerAssignment::create([
            'agent_id' => $newAgent->id,
            'customer_id' => $customer->id,
        ]);

        Sanctum::actingAs($newAgent);

        $this->getJson('/api/agent/customers')
            ->assertOk()
            ->assertJsonPath('customers.0.id', $customer->id)
            ->assertJsonPath('customers.0.latest_message', 'Previous history stays');
    }
}
