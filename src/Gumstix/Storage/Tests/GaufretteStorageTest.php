<?php

namespace Gumstix\Storage\Tests;

use Gumstix\Storage\GaufretteStorage;
use PHPUnit\Framework\TestCase;

class GaufretteStorageTest extends TestCase
{
    /** @var GaufretteStorage */
    private $storage;

    public function setUp()
    {
        parent::setUp();
        $this->storage = GaufretteStorage::local(__DIR__ .'/testfs');
    }

    public function testGet()
    {
        $contents = $this->storage->get('fileA.txt');
        $this->assertEquals('The contents of fileA.', trim($contents));
    }

    /**
     * @dataProvider existsProvider
     */
    public function testExists($key, $expected)
    {
        $result = $this->storage->exists($key);
        $this->assertSame($expected, $result);
    }

    public function existsProvider()
    {
        return [
            ['fileA.txt', true],
            ['subdir/fileB.txt', true],
            ['subdir', false],
            ['nosuchfile', false],
        ];
    }

    public function testGetSymlink()
    {
        $contents = $this->storage->get('linkA.txt');
        $this->assertEquals('The contents of fileA.', trim($contents));
    }

    public function testListKeys()
    {
        $keys = $this->storage->listKeys();
        $this->assertCount(3, $keys);
        $this->assertContains('fileA.txt', $keys);
        $this->assertContains('linkA.txt', $keys);  // a symlink
        $this->assertContains('subdir/fileB.txt', $keys);
    }

    public function testListKeysWithPrefix()
    {
        $keys = $this->storage->listKeys('subdir');
        $this->assertCount(1, $keys);
        $this->assertContains('subdir/fileB.txt', $keys);
    }

    public function testGetMimeType()
    {
        $result = $this->storage->getMimeType('fileA.txt');
        $this->assertEquals("text/plain", $result);
    }
}
