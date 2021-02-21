<?php

namespace AutozNetwork\Tests\Fixtures;

use AutozNetwork\Url\UrlDetectionInterface;

class FooUrlDetectionInterface implements UrlDetectionInterface
{
    public function getCurrentUrl()
    {
        return 'https://foo.bar';
    }
}
