<?php
// Script to ensure the asset directories exist
$directories = [
    __DIR__ . '/css',
    __DIR__ . '/js',
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        echo "Created directory: $dir\n";
    }
}

echo "Directory structure is ready for assets.\n";
