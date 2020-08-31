<?php

namespace AutozNetwork\Resource;

use AutozNetwork\ResourceBase;

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
    public function addLocation($locationData)
    {
        // return new Location($this->autozNetwork)->create($locationData);
    }
}
