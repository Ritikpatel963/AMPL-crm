<?php
// Quick diagnostic - shows actual recording URLs and tests them
// Upload this to: public/rec_debug.php
// Visit: https://amplchat.agromarket.co.in/rec_debug.php
// DELETE THIS FILE after debugging!

// Bootstrap Laravel
$basePath = dirname(__DIR__);
require $basePath . '/vendor/autoload.php';
$app = require $basePath . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$rows = DB::table('call_logs')
    ->whereNotNull('recording_url')
    ->orderByDesc('id')
    ->limit(5)
    ->get(['id', 'recording_url', 'created_at']);

echo "<h2>Latest Recording URLs</h2>";
echo "<table border='1' cellpadding='8'>";
echo "<tr><th>ID</th><th>URL</th><th>HTTP Status</th></tr>";

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
    echo "<tr>";
    echo "<td>{$row->id}</td>";
    echo "<td><a href='{$url}' target='_blank'>{$url}</a></td>";
    echo "<td style='color:{$color};font-weight:bold'>{$status}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<br><strong>APP_URL from env:</strong> " . env('APP_URL');
echo "<br><strong>Storage public path:</strong> " . storage_path('app/public');
echo "<br><strong>Public storage symlink exists:</strong> " . (file_exists(public_path('storage')) ? 'YES' : 'NO');
