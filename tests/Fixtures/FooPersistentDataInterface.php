<?php

namespace AutozNetwork\Tests\Fixtures;

use AutozNetwork\PersistentData\PersistentDataInterface;

class FooPersistentDataInterface implements PersistentDataInterface
{
    public function get($key)
    {
        return 'foo';
    }

    public function set($key, $value)
    {
    }
}
