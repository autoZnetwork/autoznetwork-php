<?php
require '../vendor/autoload.php';

$autozNetwork = new \AutozNetwork\AutozNetwork('API_GOES_HERE');

$locations = $autozNetwork->location()->all();

print_r($locations);
?>
