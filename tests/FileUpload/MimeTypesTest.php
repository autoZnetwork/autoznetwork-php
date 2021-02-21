<?php

namespace AutozNetwork\Tests\FileUpload;

use AutozNetwork\FileUpload\Mimetypes;
use PHPUnit\Framework\TestCase;

class MimetypesTest extends TestCase
{

    /**
     * Taken from Guzzle
     *
     * @see https://github.com/guzzle/guzzle/blob/master/tests/MimetypesTest.php
     */
    public function testGetsFromExtension()
    {
        $this->assertEquals('text/x-php', Mimetypes::getInstance()->fromExtension('php'));
    }

    public function testGetsFromFilename()
    {
        $this->assertEquals('text/x-php', Mimetypes::getInstance()->fromFilename(__FILE__));
    }

    public function testGetsFromCaseInsensitiveFilename()
    {
        $this->assertEquals('text/x-php', Mimetypes::getInstance()->fromFilename(strtoupper(__FILE__)));
    }

    public function testReturnsNullWhenNoMatchFound()
    {
        $this->assertNull(Mimetypes::getInstance()->fromExtension('foobar'));
    }
}
