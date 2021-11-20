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
        $this->pages->method("getCount")->willReturn(1);
        $this->pages->method("level")->willReturn(1);
        $this->pages->method("heading")->willReturn("Start");
        $this->pageData = $this->createMock(PageData::class);
        $this->pageData->method("find_page")->willReturnOnConsecutiveCalls([], ["coco_id" => "12345"]);
        $this->idGenerator = $this->createMock(IdGenerator::class);
        $this->idGenerator->method("newId")->willReturn("12345");
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
        $this->assertTrue($this->subject->save("foo", 0, "hello world"));
        $this->assertSame(["hello world"], $this->subject->findAll("foo", 0));
    }

    public function testFindNothing()
    {
        $this->assertFalse($this->subject->find("foo", 0));
    }

    public function testFind()
    {
        $this->assertTrue($this->subject->save("foo", 0, "hello world"));
        $this->assertSame("hello world", $this->subject->find("foo", 0));
    }
}
