<?php

namespace AutozNetwork\Rest;

use AutozNetwork\ResourceBase;

class Feed extends ResourceBase
{
    /**
     * Get a list of vendors with pre-formatted schemas that are availble for the feed.
     */
    public function getAvailableVendors()
    {
        return $this->sendRequest(
            'GET',
            $this->resourceName().'/vendor',
            [],
            []
        );
    }
}
