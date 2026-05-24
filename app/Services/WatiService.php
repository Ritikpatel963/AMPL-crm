<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WatiService
{
    private string $endpoint;
    private string $token;
    private string $template;
    private string $channelNumber;

    public function __construct()
    {
        $this->endpoint      = rtrim(config('wati.endpoint'), '/');
        $this->token         = config('wati.token');
        $this->template      = config('wati.otp_template', 'otp');
        $this->channelNumber = config('wati.channel_number');
    }

    public function sendOtp(string $phone, string $otp): bool
    {
        // ── Normalize phone to 91XXXXXXXXXX format ──────────────────────────
        $phone = trim($phone);
        $phone = ltrim($phone, '+');           // remove + if present
        $phone = preg_replace('/^91/', '', $phone); // remove 91 if present
        $phone = '91' . $phone;                // always add 91 back
        // Result: always "916261116225" format ✅
        // ─────────────────────────────────────────────────────────────────────

        Log::info('[WATI] Sending to phone', [
            'cleaned'  => $phone,
            'endpoint' => $this->endpoint,
            'template' => $this->template,
            'channel'  => $this->channelNumber,
        ]);

        $url = "{$this->endpoint}/api/v2/sendTemplateMessages";

        $payload = [
            'template_name'  => $this->template,
            'broadcast_name' => 'vendor_otp_' . time(),
            'receivers'      => [
                [
                    'whatsappNumber' => $phone,
                    'localMessageId' => 'otp_' . uniqid(),
                    'customParams'   => [
                        ['name' => '1', 'value' => $otp],
                        ['name' => '2', 'value' => $otp],
                    ],
                ],
            ],
            'channel_number' => $this->channelNumber,
        ];

        Log::info('[WATI] OTP payload prepared', [
            'phone' => $phone,
            'template' => $this->template,
        ]);

        try {
            $response = Http::withToken($this->token)
                ->timeout(10)
                ->post($url, $payload);

            if ($response->successful()) {
                Log::info('[WATI] ✅ OTP sent successfully', [
                    'phone' => $phone,
                ]);
                return true;
            }

            Log::error('[WATI] ❌ OTP send failed', [
                'phone'  => $phone,
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return false;

        } catch (\Exception $e) {
            Log::error('[WATI] ❌ OTP exception', [
                'phone'   => $phone,
                'message' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
