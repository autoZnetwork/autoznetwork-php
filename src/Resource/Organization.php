<?php

namespace AutozNetwork\Resource;

use AutozNetwork\ResourceBase;
use AutozNetwork\Resource\Location;

class Organization extends ResourceBase
{
  public function location()
  {
      // return $this->autozNetwork;
      return new Location($this->autozNetwork);
  }
  /**
   * Add a new location to an Organization
   */
  function addLocation($locationData) {
    // return new Location($this->autozNetwork)->create($locationData);
  }
}