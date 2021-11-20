<?php

/**
 * Copyright 2021 Christoph M. Becker
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

namespace Coco;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use XH\PageDataRouter as PageData;
use XH\Pages;

final class CocoServiceTest extends TestCase
{
    /** @var vfsStreamDirectory */
    private $root;

    /** @var Pages */
    private $pages;

    /** @var PageData&MockObject */
    private $pageData;

    /** @var CocoService */
    private $subject;

    /** @var IdGenerator */
    private $idGenerator;

    public function setup(): void
    {
        $this->root = vfsStream::setup("test");
        $this->pages = $this->createMock(Pages::class);
        $this->pages->method("getCount")->willReturn(2);
        $this->pages->method("level")->willReturnMap([[0, 1], [1, 2]]);
        $this->pages->method("heading")->willReturnMap([[0, "Start"], [1, "Sub"]]);
        $this->pageData = $this->createMock(PageData::class);
        $this->idGenerator = $this->createMock(IdGenerator::class);
        $this->subject = new CocoService(
            vfsStream::url("test/coco"),
            "",
            $this->pages,
            $this->pageData,
            $this->idGenerator
        );
    }

    public function testDataDirIsCreated()
    {
        $this->assertTrue($this->root->hasChild("coco"));
    }

    public function testDataDir()
    {
        $this->assertSame(vfsStream::url("test/coco"), $this->subject->dataDir());
    }

    public function testFilename()
    {
        $this->assertSame(vfsStream::url("test/coco") . "/foo.htm", $this->subject->filename("foo"));
    }

    public function testFindAllNames()
    {
        $this->assertTrue($this->subject->save("foo", 0, "hello world"));
        $this->assertTrue($this->subject->save("bar", 0, "hello world"));
        $this->assertSame(["foo", "bar"], $this->subject->findAllNames());
    }

    public function testFindAllNothing()
    {
        $this->assertFalse($this->subject->findAll("foo", 0));
    }

    public function testFindAll()
    {
        $this->idGenerator->method("newId")->willReturnOnConsecutiveCalls("12345", "23456");
        $this->pageData->method("find_page")->willReturnOnConsecutiveCalls([], [], ["coco_id" => "12345"], ["coco_id" => "23456"]);
        $this->assertTrue($this->subject->save("foo", 0, "hello world"));
        $this->assertSame(["hello world", ""], $this->subject->findAll("foo", 0));
    }

    public function testFindNothing()
    {
        $this->assertFalse($this->subject->find("foo", 0));
    }

    public function testFind()
    {
        $this->idGenerator->method("newId")->willReturnOnConsecutiveCalls("12345", "23456");
        $this->pageData->method("find_page")->willReturnOnConsecutiveCalls([], [], ["coco_id" => "12345"]);
        $this->assertTrue($this->subject->save("foo", 0, "hello world"));
        $this->assertSame("hello world", $this->subject->find("foo", 0));
    }

    public function testDelete()
    {
        $this->assertTrue($this->subject->save("foo", 0, "hello world"));
        $this->assertTrue($this->subject->save("bar", 0, "hello world"));
        $this->assertSame([$this->subject->filename("foo") => true], $this->subject->delete("foo"));
        $this->assertSame(["bar"], $this->subject->findAllNames());
    }
}
