<?php
// Simple path test file
echo json_encode([
    'success' => true,
    'message' => 'Path test successful',
    'directory' => __DIR__,
    'file' => __FILE__
]);
