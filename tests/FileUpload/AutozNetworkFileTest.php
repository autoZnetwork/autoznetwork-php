<?php

namespace AutozNetwork\Tests\FileUpload;

use AutozNetwork\FileUpload\AutozNetworkFile;
use PHPUnit\Framework\TestCase;

class AutozNetworkFileTest extends TestCase
{
    protected $testFile = '';

    protected function setUp(): void
    {
        $this->testFile = __DIR__ . '/../foo.txt';
    }

    public function testCanOpenAndReadAndCloseAFile()
    {
        $file = new AutozNetworkFile($this->testFile);
        $fileContents = $file->getContents();

        $this->assertEquals('This is a text file used for testing.', trim($fileContents, "\n"));
    }

    public function testPartialFilesCanBeCreated()
    {
        $file = new AutozNetworkFile($this->testFile, 14, 5);
        $fileContents = $file->getContents();

        $this->assertEquals('is a text file', $fileContents);
    }

    public function testTryingToOpenAFileThatDoesntExistsThrows()
    {
        $this->expectException(\AutozNetwork\Exceptions\AutozNetworkSDKException::class);

        new AutozNetworkFile('does_not_exist.file');
    }
}
