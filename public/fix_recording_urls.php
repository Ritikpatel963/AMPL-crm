<?php
// Fixes recording URLs in database: replaces wrong UAT domain with correct live domain
// Upload to: public/fix_recording_urls.php
// Run ONCE at: https://amplchat.agromarket.co.in/fix_recording_urls.php
// DELETE IMMEDIATELY AFTER!

$basePath = dirname(__DIR__);
require $basePath . '/vendor/autoload.php';
$app = require $basePath . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$wrong  = 'https://uatamplchat.agromarket.co.in';
$right  = 'https://amplchat.agromarket.co.in';

// Count how many need fixing
$count = DB::table('call_logs')
    ->where('recording_url', 'like', $wrong . '%')
    ->count();

echo "<h2>Recording URL Fix Script</h2>";
echo "<p>Found <strong>{$count}</strong> recordings with wrong domain.</p>";

if ($count > 0) {
    // Fix them all
    $updated = DB::table('call_logs')
        ->where('recording_url', 'like', $wrong . '%')
        ->update([
            'recording_url' => DB::raw("REPLACE(recording_url, '{$wrong}', '{$right}')")
        ]);

    echo "<p style='color:green;font-weight:bold'>✅ Fixed {$updated} recording URLs!</p>";
} else {
    echo "<p style='color:orange'>No URLs needed fixing (already correct or none found).</p>";
}

// Show updated records
$rows = DB::table('call_logs')
    ->whereNotNull('recording_url')
    ->orderByDesc('id')
    ->limit(5)
    ->get(['id', 'recording_url']);

echo "<h3>Latest recording URLs (after fix):</h3>";
echo "<table border='1' cellpadding='8'>";
echo "<tr><th>ID</th><th>URL</th><th>Test</th></tr>";
foreach ($rows as $row) {
    $url = $row->recording_url;
    $ch  = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $color = $status === 200 ? 'green' : 'red';
    echo "<tr><td>{$row->id}</td><td><a href='{$url}' target='_blank'>{$url}</a></td><td style='color:{$color};font-weight:bold'>{$status}</td></tr>";
}
echo "</table>";

echo "<hr><p><strong>⚠ DELETE THIS FILE FROM SERVER NOW!</strong></p>";
