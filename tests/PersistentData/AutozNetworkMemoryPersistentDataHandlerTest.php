<?php

namespace AutozNetwork\Tests\PersistentData;

use AutozNetwork\PersistentData\AutozNetworkMemoryPersistentDataHandler;
use PHPUnit\Framework\TestCase;

class AutozNetworkMemoryPersistentDataHandlerTest extends TestCase
{
    public function testCanGetAndSetAVirtualValue()
    {
        $handler = new AutozNetworkMemoryPersistentDataHandler();
        $handler->set('foo', 'bar');
        $value = $handler->get('foo');

        $this->assertEquals('bar', $value);
    }

    public function testGettingAValueThatDoesntExistWillReturnNull()
    {
        $handler = new AutozNetworkMemoryPersistentDataHandler();
        $value = $handler->get('does_not_exist');

        $this->assertNull($value);
    }
}
