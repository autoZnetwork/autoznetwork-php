<?php
require '../vendor/autoload.php';

$autozNetwork = new \AutozNetwork\AutozNetwork('API_GOES_HERE');

$location = [
  'name' => 'New Location'
];
// Need to be able to pass the Organization id: organization('idHere')
// $location = $autozNetwork->organization('')->location()->create($location);

$location = $autozNetwork->organization()->location()->create($location);

print_r($location);
?>
