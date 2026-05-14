<?php
$file = __DIR__ . '/IMPLEMENTATION_PLAN.md';
$content = file_get_contents($file);

$replacements = [
    'Manajer' => 'Owner',
    'manajer' => 'owner',
    'Manager' => 'Owner',
    'manager' => 'owner',
    'Cafe' => 'Toko',
    'cafe' => 'toko',
    'store_id' => 'toko_id',
    'store' => 'toko',
];

foreach ($replacements as $search => $replace) {
    $content = str_replace($search, $replace, $content);
}

file_put_contents($file, $content);
echo "IMPLEMENTATION_PLAN.md updated.\n";
