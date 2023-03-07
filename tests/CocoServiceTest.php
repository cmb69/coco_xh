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

namespace Coco;

use Coco\Infra\CocoService;
use Coco\Infra\FakePages;
use Coco\Infra\IdGenerator;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;

class CocoServiceTest extends TestCase
{
    /** @var vfsStreamDirectory */
    private $root;

    /** @var IdGenerator */
    private $idGenerator;

    public function setup(): void
    {
        $this->root = vfsStream::setup("test");
        $this->idGenerator = $this->createMock(IdGenerator::class);
    }

    public function testDataDirIsCreated()
    {
        $this->assertEquals(vfsStream::url("test/coco"), $this->sut()->dataDir());
        $this->assertTrue($this->root->hasChild("coco"));
    }

    public function testDataDir()
    {
        $this->assertSame(vfsStream::url("test/coco"), $this->sut()->dataDir());
    }

    public function testFilename()
    {
        $this->assertSame(vfsStream::url("test/coco") . "/foo.htm", $this->sut()->filename("foo"));
    }

    public function testFindAllNames()
    {
        $sut = $this->sut();
        $this->assertTrue($sut->save("foo", 0, "hello world"));
        $this->assertTrue($sut->save("bar", 0, "hello world"));
        $this->assertSame(["foo", "bar"], $sut->findAllNames());
    }

    public function testFindAllNothing()
    {
        $this->assertEmpty(iterator_to_array($this->sut()->findAll("foo", 0)));
    }

    public function testFindAll()
    {
        $this->idGenerator->method("newId")->willReturnOnConsecutiveCalls("12345", "23456");
        $sut = $this->sut(["pages" => ["data" => [[], [], ["coco_id" => "12345"], ["coco_id" => "23456"]]]]);
        $this->assertTrue($sut->save("foo", 0, "hello world"));
        $this->assertSame(["hello world", ""], iterator_to_array($sut->findAll("foo", 0)));
    }

    public function testFindNothing()
    {
        $this->assertEmpty($this->sut()->find("foo", 0));
    }

    public function testFind()
    {
        $this->idGenerator->method("newId")->willReturnOnConsecutiveCalls("12345", "23456");
        $sut = $this->sut(["pages" => ["data" => [[], [], ["coco_id" => "12345"]]]]);
        $this->assertTrue($sut->save("foo", 0, "hello world"));
        $this->assertSame("hello world", $sut->find("foo", 0));
    }

    public function testDelete()
    {
        $sut = $this->sut();
        $this->assertTrue($sut->save("foo", 0, "hello world"));
        $this->assertTrue($sut->save("bar", 0, "hello world"));
        $this->assertSame([], $sut->delete("foo"));
        $this->assertSame(["bar"], $this->sut()->findAllNames());
    }

    private function sut($options = []): CocoService
    {
        $pageOptions = ($options["pages"] ?? []) + ["count" => 2, "levels" => [1, 2], "headings" => ["Start", "Sub"]];
        return new CocoService(
            vfsStream::url("test/coco"),
            "",
            new FakePages($pageOptions),
            $this->idGenerator
        );
    }
}
