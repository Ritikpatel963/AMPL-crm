<?php

namespace App\Services;

use TaylanUnutmaz\AgoraTokenBuilder\RtcTokenBuilder;
use Carbon\Carbon;

class AgoraService
{
    public static function generateToken(string $channel, int $uid): string
    {
        $appId = config('services.agora.app_id');
        $appCertificate = config('services.agora.app_certificate');

        // CRITICAL: Token must be valid for longer than expected call duration
        $expireSeconds = 3600; // 1 hour
        $privilegeExpiredTs = Carbon::now()->timestamp + $expireSeconds;

        // IMPORTANT: uid must match EXACTLY what's used in joinChannel()
        // uid CANNOT be 0 when using token authentication
        if ($uid <= 0) {
            throw new \InvalidArgumentException('UID must be a positive integer when using token authentication');
        }

        return RtcTokenBuilder::buildTokenWithUid(
            $appId,
            $appCertificate,
            $channel,
            $uid,
            RtcTokenBuilder::RolePublisher,
            $privilegeExpiredTs
        );
    }
}