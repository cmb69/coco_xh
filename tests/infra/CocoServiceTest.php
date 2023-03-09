<?php

/**
 * Copyright 2021-2023 Christoph M. Becker
 *
 * This file is part of Coco_XH.
 *
 * Coco_XH is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Coco_XH is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Coco_XH.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Coco\Infra;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;

class CocoServiceTest extends TestCase
{
    /** @var vfsStreamDirectory */
    private $root;

    public function setup(): void
    {
        $this->root = vfsStream::setup("test");
    }

    public function testDataDirIsCreated()
    {
        $sut = new CocoService(vfsStream::url("test/coco"), "", $this->pages(), $this->idGenerator());;
        $this->assertEquals(vfsStream::url("test/coco"), $sut->dataDir());
        $this->assertTrue($this->root->hasChild("coco"));
    }

    public function testDataDir()
    {
        $sut = new CocoService(vfsStream::url("test/coco"), "", $this->pages(), $this->idGenerator());;
        $this->assertSame(vfsStream::url("test/coco"), $sut->dataDir());
    }

    public function testFilename()
    {
        $sut = new CocoService(vfsStream::url("test/coco"), "", $this->pages(), $this->idGenerator());;
        $this->assertSame(vfsStream::url("test/coco") . "/foo.htm", $sut->filename("foo"));
    }

    public function testFindAllNames()
    {
        $sut = new CocoService(vfsStream::url("test/coco"), "", $this->pages(), $this->idGenerator());;
        $this->assertTrue($sut->save("foo", 0, "hello world"));
        $this->assertTrue($sut->save("bar", 0, "hello world"));
        $this->assertSame(["foo", "bar"], $sut->findAllNames());
    }

    public function testFindAllNothing()
    {
        $sut = new CocoService(vfsStream::url("test/coco"), "", $this->pages(), $this->idGenerator());;
        $this->assertEmpty(iterator_to_array($sut->findAll("foo", 0)));
    }

    public function testFindAll()
    {
        $sut = new CocoService(vfsStream::url("test/coco"), "", $this->pages(), $this->idGenerator());;
        $this->assertTrue($sut->save("foo", 0, "hello world"));
        $this->assertSame(["hello world", ""], iterator_to_array($sut->findAll("foo", 0)));
    }

    public function testFindNothing()
    {
        $sut = new CocoService(vfsStream::url("test/coco"), "", $this->pages(), $this->idGenerator());;
        $this->assertEmpty($sut->find("foo", 0));
    }

    public function testFind()
    {
        $sut = new CocoService(vfsStream::url("test/coco"), "", $this->pages(), $this->idGenerator());;
        $this->assertTrue($sut->save("foo", 0, "hello world"));
        $this->assertSame("hello world", $sut->find("foo", 0));
    }

    public function testDelete()
    {
        $sut = new CocoService(vfsStream::url("test/coco"), "", $this->pages(), $this->idGenerator());;
        $this->assertTrue($sut->save("foo", 0, "hello world"));
        $this->assertTrue($sut->save("bar", 0, "hello world"));
        $this->assertSame([], $sut->delete("foo"));
        $this->assertSame(["bar"], $sut->findAllNames());
    }

    public function testFailureToDeleteIsReported(): void
    {
        $filename = vfsStream::url("test/coco/foo.htm");
        mkdir(dirname($filename), 0777, true);
        touch($filename);
        chmod(dirname($filename), 0444);
        $sut = new CocoService(vfsStream::url("test/coco"), "", $this->pages(), $this->idGenerator());;
        $result = $sut->delete("foo");
        $this->assertEquals([$filename], $result);
    }

    public function testTryingToDeleteNonExistingFileDoesNotRaiseWarning(): void
    {
        $filename = vfsStream::url("test/coco/20230307_120000_foo.htm");
        mkdir(dirname($filename), 0777, true);
        mkdir($filename);
        $sut = new CocoService(vfsStream::url("test/coco"), "", $this->pages(), $this->idGenerator());;
        $result = $sut->delete("foo");
        $this->assertEquals(["vfs://test/coco/20230307_120000_foo.htm", "vfs://test/coco/foo.htm"], $result);
    }

    private function pages(): Pages
    {
        $pages = $this->createMock(Pages::class);
        $pages->method("count")->willReturn(2);
        $pages->method("level")->willReturnMap([[0, 1], [1, 2]]);
        $pages->method("heading")->willReturnMap([[0, "Start"], [1, "Sub"]]);
        $pages->method("data")->willReturnOnConsecutiveCalls([], [], ["coco_id" => "12345"], ["coco_id" => "23456"]);
        return $pages;
    }

    private function idGenerator(): IdGenerator
    {
        $idGenerator = $this->createMock(IdGenerator::class);
        $idGenerator->method("newId")->willReturnOnConsecutiveCalls("12345", "23456");
        return $idGenerator;
    }
}
