<?php
require '../vendor/autoload.php';

$autozNetwork = new \AutozNetwork\AutozNetwork('API_GOES_HERE');

$products = $autozNetwork->product()->all();

print_r($products);
?>
