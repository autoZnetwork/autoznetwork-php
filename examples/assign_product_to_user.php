<?php
require '../vendor/autoload.php';

$autozNetwork = new \AutozNetwork\AutozNetwork('API_GOES_HERE');

$products = $autozNetwork->product()->all();

// Get the first product
// Get a user
$user = '';
// Add the product to the user

print_r($user);
?>
