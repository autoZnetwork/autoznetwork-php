<?php

namespace AutozNetwork\Tests\Fixtures;

use AutozNetwork\PseudoRandomString\PseudoRandomStringGeneratorInterface;

class FooBarPseudoRandomStringGenerator implements PseudoRandomStringGeneratorInterface
{
    public function getPseudoRandomString($length)
    {
        return 'csprs123';
    }
}
