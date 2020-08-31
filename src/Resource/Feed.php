<?php

namespace AutozNetwork\Resource;

use AutozNetwork\ResourceBase;

class Feed extends ResourceBase
{
  /**
   * Get a list of vendors with pre-formatted schemas that are availble for the feed.
   */
  function getAvailableVendors() {
    return $this->sendRequest(
        'GET',
        $this->resourceName().'/vendor',
        array(),
        array()
    );
  }
}