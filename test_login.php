<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$request = Illuminate\Http\Request::create('/api/agent/login/verify', 'POST', [
    'phone_number' => '919630884927',
    'otp' => 'password'
]);

$response = app()->handle($request);
echo "Status: " . $response->getStatusCode() . "\n";
echo "Content: " . $response->getContent() . "\n";
