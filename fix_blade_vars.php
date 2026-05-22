<?php
$files = glob('resources/views/admin_panel/callingcrm/*.blade.php');
foreach ($files as $file) {
    $html = file_get_contents($file);
    $basename = basename($file, '.blade.php');
    
    // Determine the actual string value
    $jsModule = 'crm-core.js';
    if (in_array($basename, ['contact', 'contact-properties'])) $jsModule = 'leads.js';
    if ($basename === 'pipeline') $jsModule = 'pipeline.js';
    if (in_array($basename, ['dashboard', 'report', 'user-report', 'login-report', 'trends'])) $jsModule = 'campaigns.js';
    if ($basename === 'settings') $jsModule = 'agents.js';

    // Replace the literal PHP variable with the evaluated string
    $html = str_replace(
        "<script type=\"module\" src=\"{{ asset('js/crm/' . \$jsModule) }}\"></script>",
        "<script type=\"module\" src=\"{{ asset('js/crm/" . $jsModule . "') }}\"></script>",
        $html
    );
    
    file_put_contents($file, $html);
}
echo "Fixed Blade syntax errors!";
