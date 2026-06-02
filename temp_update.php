<?php
$files = array_merge(
    glob(__DIR__ . '/app/Http/Controllers/Api/CallingCrm/*.php'),
    glob(__DIR__ . '/app/Http/Controllers/Api/*.php')
);
foreach ($files as $file) {
    $content = file_get_contents($file);
    
    // Replace $request->integer(...)
    $newContent = preg_replace_callback(
        '/->paginate\(\$request->integer\(\'per_page\',\s*(\d+)\)\)/',
        function ($matches) {
            return "->paginate(min(max(\$request->integer('per_page', {$matches[1]}), 1), 100))";
        },
        $content
    );
    
    // Replace request()->integer(...)
    $newContent = preg_replace_callback(
        '/->paginate\(request\(\)->integer\(\'per_page\',\s*(\d+)\)\)/',
        function ($matches) {
            return "->paginate(min(max(request()->integer('per_page', {$matches[1]}), 1), 100))";
        },
        $newContent
    );
    
    if ($content !== $newContent) {
        file_put_contents($file, $newContent);
        echo "Updated $file\n";
    }
}
echo "Done.\n";
