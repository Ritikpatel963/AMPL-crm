<?php

namespace App\Http\Controllers\Api\CallingCrm;

use App\Http\Controllers\Controller;
use App\Models\CommunicationEvent;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CommunicationsController extends Controller
{
    public function index(Request $request)
    {
        $events = CommunicationEvent::with(['lead:id,name,phone', 'campaign:id,name', 'user:id,name'])
            ->when($request->filled('lead_id'), fn ($q) => $q->where('lead_id', $request->lead_id))
            ->when($request->filled('campaign_id'), fn ($q) => $q->where('campaign_id', $request->campaign_id))
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->user_id))
            ->when($request->filled('channel'), fn ($q) => $q->where('channel', $request->channel))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('sent_at', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('sent_at', '<=', $request->to))
            ->latest('sent_at')
            ->paginate($request->integer('per_page', 25));

        return response()->json(['status' => true, 'data' => $events]);
    }

    public function sendSms(Request $request)
    {
        $data = $request->validate([
            'lead_id' => ['nullable', 'exists:leads,id'],
            'campaign_id' => ['nullable', 'exists:campaigns,id'],
            'recipient' => ['required', 'string', 'max:180'],
            'body_preview' => ['nullable', 'string', 'max:500'],
        ]);

        $event = CommunicationEvent::create([
            'lead_id' => $data['lead_id'] ?? null,
            'campaign_id' => $data['campaign_id'] ?? null,
            'user_id' => auth()->id(),
            'channel' => 'sms',
            'direction' => 'outgoing',
            'status' => 'sent',
            'recipient' => $data['recipient'],
            'body_preview' => $data['body_preview'] ?? null,
            'sent_at' => now(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'SMS sent successfully',
            'data' => $event,
        ], 201);
    }

    public function sendEmail(Request $request)
    {
        $data = $request->validate([
            'lead_id' => ['nullable', 'exists:leads,id'],
            'campaign_id' => ['nullable', 'exists:campaigns,id'],
            'recipient' => ['required', 'email', 'max:180'],
            'body_preview' => ['nullable', 'string', 'max:500'],
        ]);

        $event = CommunicationEvent::create([
            'lead_id' => $data['lead_id'] ?? null,
            'campaign_id' => $data['campaign_id'] ?? null,
            'user_id' => auth()->id(),
            'channel' => 'email',
            'direction' => 'outgoing',
            'status' => 'sent',
            'recipient' => $data['recipient'],
            'body_preview' => $data['body_preview'] ?? null,
            'sent_at' => now(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Email sent successfully',
            'data' => $event,
        ], 201);
    }

    public function sendWhatsApp(Request $request)
    {
        $data = $request->validate([
            'lead_id' => ['nullable', 'exists:leads,id'],
            'campaign_id' => ['nullable', 'exists:campaigns,id'],
            'recipient' => ['required', 'string', 'max:180'],
            'template_id' => ['nullable', 'string', 'max:120'],
            'body_preview' => ['nullable', 'string', 'max:500'],
        ]);

        $event = CommunicationEvent::create([
            'lead_id' => $data['lead_id'] ?? null,
            'campaign_id' => $data['campaign_id'] ?? null,
            'user_id' => auth()->id(),
            'channel' => 'whatsapp',
            'direction' => 'outgoing',
            'status' => 'sent',
            'recipient' => $data['recipient'],
            'template_id' => $data['template_id'] ?? null,
            'body_preview' => $data['body_preview'] ?? null,
            'sent_at' => now(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'WhatsApp sent successfully',
            'data' => $event,
        ], 201);
    }
}
