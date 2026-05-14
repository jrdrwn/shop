<?php

$directories = [
    __DIR__ . '/app',
    __DIR__ . '/resources',
    __DIR__ . '/tests',
    __DIR__ . '/database/seeders',
    __DIR__ . '/database/factories',
    __DIR__ . '/routes',
];

$files = [];

function getFiles($dir, &$files) {
    if (!is_dir($dir)) return;
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) getFiles($path, $files);
        else if (is_file($path)) $files[] = $path;
    }
}

foreach ($directories as $dir) {
    getFiles($dir, $files);
}

$count = 0;
foreach ($files as $file) {
    $ext = pathinfo($file, PATHINFO_EXTENSION);
    if (in_array($ext, ['php', 'ts', 'tsx', 'js', 'css', 'json'])) {
        $content = file_get_contents($file);
        $newContent = $content;
        
        // Exact case replacements
        $newContent = str_replace('CafeOwner', 'TokoOwner', $newContent);
        $newContent = str_replace('cafeOwner', 'tokoOwner', $newContent);
        $newContent = str_replace('Cafe', 'Toko', $newContent);
        $newContent = str_replace('cafe', 'toko', $newContent);
        $newContent = str_replace('CAFE', 'TOKO', $newContent);
        
        if ($content !== $newContent) {
            file_put_contents($file, $newContent);
            echo "Updated: " . str_replace(__DIR__ . DIRECTORY_SEPARATOR, '', $file) . "\n";
            $count++;
        }
    }
}

echo "Total files updated: $count\n";
