<?php
require_once 'vendor/autoload.php';
require_once 'config/app.php';

use App\Models\Destination;

$destModel = new Destination();
$featured = $destModel->getFeatured(20);

echo "Featured destinations currently in database:\n";
foreach($featured as $dest) {
    echo "- " . $dest['name'] . " (ID: " . $dest['id'] . ")\n";
}

if (empty($featured)) {
    echo "No featured destinations found!\n";
}
