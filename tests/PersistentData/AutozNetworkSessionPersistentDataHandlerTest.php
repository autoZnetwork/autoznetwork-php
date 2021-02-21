<?php

namespace AutozNetwork\Tests\PersistentData;

use AutozNetwork\PersistentData\AutozNetworkSessionPersistentDataHandler;
use PHPUnit\Framework\TestCase;

class AutozNetworkSessionPersistentDataHandlerTest extends TestCase
{
    public function testInactiveSessionsWillThrow()
    {
        $this->expectException(\AutozNetwork\Exceptions\AutozNetworkSDKException::class);

        new AutozNetworkSessionPersistentDataHandler();
    }

    public function testCanSetAValue()
    {
        $handler = new AutozNetworkSessionPersistentDataHandler($enableSessionCheck = false);
        $handler->set('foo', 'bar');

        $this->assertEquals('bar', $_SESSION['AZN_foo']);
    }

    public function testCanGetAValue()
    {
        $_SESSION['AZN_faz'] = 'baz';
        $handler = new AutozNetworkSessionPersistentDataHandler($enableSessionCheck = false);
        $value = $handler->get('faz');

        $this->assertEquals('baz', $value);
    }

    public function testGettingAValueThatDoesntExistWillReturnNull()
    {
        $handler = new AutozNetworkSessionPersistentDataHandler($enableSessionCheck = false);
        $value = $handler->get('does_not_exist');

        $this->assertNull($value);
    }
}
