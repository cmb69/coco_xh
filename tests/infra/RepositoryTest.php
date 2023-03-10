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

use ApprovalTests\Approvals;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class RepositoryTest extends TestCase
{
    public function testCreatesDataDirOnAccess(): void
    {
        vfsStream::setup("test");
        $sut = new Repository("vfs://test/coco/", "", $this->pages(), $this->idGenerator());
        $this->assertEquals("vfs://test/coco/", $sut->dataFolder());
        $this->assertDirectoryExists("vfs://test/coco/");
    }

    public function testFilenameIsCorrect(): void
    {
        vfsStream::setup("test");
        $sut = new Repository("vfs://test/coco/", "", $this->pages(), $this->idGenerator());
        $this->assertSame("vfs://test/coco/foo.htm", $sut->filename("foo"));
    }

    public function testBackupFilenameIsCorrect(): void
    {
        vfsStream::setup("root");
        $sut = new Repository("vfs://test/coco/", "", $this->pages(), $this->idGenerator());
        $filename = $sut->filename("test", "20230309_224602");
        $this->assertEquals("vfs://test/coco/20230309_224602_test.htm", $filename);
    }

    public function testSavesCoco(): void
    {
        vfsStream::setup("test");
        $sut = new Repository("vfs://test/coco/", "", $this->pages(true), $this->idGenerator());
        $sut->save("foo", 0, "<p>some content</p>");
        $sut->save("foo", 1, "<p>other content</p>");
        Approvals::verifyHtml(file_get_contents("vfs://test/coco/foo.htm"));
    }

    public function testCanSaveEmptyContent(): void
    {
        vfsStream::setup("test");
        $sut = new Repository("vfs://test/coco/", "", $this->pages(true), $this->idGenerator());
        $sut->save("foo", 0, "");
        Approvals::verifyHtml(file_get_contents("vfs://test/coco/foo.htm"));
    }

    public function testSavingFailsIfFileIsNotWritable(): void
    {
        vfsStream::setup("test");
        mkdir("vfs://test/coco/");
        mkdir("vfs://test/coco/foo.htm");
        $sut = new Repository("vfs://test/coco/", "", $this->pages(true), $this->idGenerator());
        $result = $sut->save("foo", 0, "<p>some content</p>");
        $this->assertFalse($result);
    }

    public function testFindsAllNames(): void
    {
        vfsStream::setup("test");
        mkdir("vfs://test/coco/");
        touch("vfs://test/coco/foo.htm");
        touch("vfs://test/coco/bar.htm");
        $sut = new Repository("vfs://test/coco/", "", $this->pages(), $this->idGenerator());
        $names = $sut->findAllNames();
        $this->assertSame(["bar", "foo"], $names);
    }

    public function testFindsAllCocontents(): void
    {
        vfsStream::setup("test");
        mkdir("vfs://test/coco/");
        copy(__DIR__ . "/approvals/RepositoryTest.testSavesCoco.approved.html", "vfs://test/coco/foo.htm");
        $sut = new Repository("vfs://test/coco/", "", $this->pages(), $this->idGenerator());
        $result = $sut->findAll("foo");
        $this->assertSame(["<p>some content</p>", "<p>other content</p>"], iterator_to_array($result));
    }

    public function testFindsEmptyCoContensIfIdsAreMissing(): void
    {
        vfsStream::setup("test");
        mkdir("vfs://test/coco/");
        copy(__DIR__ . "/approvals/RepositoryTest.testSavesCoco.approved.html", "vfs://test/coco/foo.htm");
        $sut = new Repository("vfs://test/coco/", "", $this->pages(true), $this->idGenerator());
        $result = $sut->findAll("foo");
        $this->assertSame(["", ""], iterator_to_array($result));
    }

    public function testFindsEmptyArrayIfCocoFileDoesNotExist(): void
    {
        vfsStream::setup("test");
        $sut = new Repository("vfs://test/coco/", "", $this->pages(), $this->idGenerator());
        $result = $sut->findAll("foo");
        $this->assertEquals([], iterator_to_array($result));
    }

    public function testFindsAllBackups(): void
    {
        vfsStream::setup("test");
        mkdir("vfs://test/coco/");
        touch("vfs://test/coco/20230309_120000_foo.htm");
        touch("vfs://test/coco/20230306_120000_test.htm");
        touch("vfs://test/coco/20230307_120000_test.htm");
        touch("vfs://test/coco/20230309_120000_test.htm");
        touch("vfs://test/coco/20230309-120000_test.htm");
        $expected = [
            ["test", "20230306_120000"],
            ["test", "20230307_120000"],
            ["test", "20230309_120000"],
        ];
        $sut = new Repository("vfs://test/coco/", "", $this->pages(), $this->idGenerator());
        $actual = $sut->findAllBackups("test");
        $this->assertEquals($expected, $actual);
    }

    public function testFindsCoContent(): void
    {
        vfsStream::setup("test");
        mkdir("vfs://test/coco/");
        copy(__DIR__ . "/approvals/RepositoryTest.testSavesCoco.approved.html", "vfs://test/coco/foo.htm");
        $sut = new Repository("vfs://test/coco/", "", $this->pages(), $this->idGenerator());
        $result = $sut->find("foo", 0);
        $this->assertSame("<p>some content</p>", $result);
    }

    public function testFindsEmptyCoContentIfIdIsMissing(): void
    {
        vfsStream::setup("test");
        mkdir("vfs://test/coco/");
        copy(__DIR__ . "/approvals/RepositoryTest.testSavesCoco.approved.html", "vfs://test/coco/foo.htm");
        $sut = new Repository("vfs://test/coco/", "", $this->pages(true), $this->idGenerator());
        $result = $sut->find("foo", 0);
        $this->assertSame("", $result);
    }

    public function testFindsEmptyStringIfCocoFileDoesNotExist(): void
    {
        vfsStream::setup("test");
        $sut = new Repository("vfs://test/coco/", "", $this->pages(), $this->idGenerator());
        $result = $sut->find("foo", 0);
        $this->assertEquals("", $result);
    }

    public function testCreatesBackup(): void
    {
        vfsStream::setup("test");
        mkdir("vfs://test/coco/");
        touch("vfs://test/coco/test.htm");
        $sut = new Repository("vfs://test/coco/", "", $this->pages(), $this->idGenerator());
        $result = $sut->backup("test", "20230309_224602");
        $this->assertTrue($result);
        $this->assertFileExists("vfs://test/coco/20230309_224602_test.htm");
    }

    public function testDeletesCoContents(): void
    {
        vfsStream::setup("test");
        mkdir("vfs://test/coco/");
        touch("vfs://test/coco/test.htm");
        $sut = new Repository("vfs://test/coco/", "", $this->pages(), $this->idGenerator());
        $actual = $sut->delete("test");
        $this->assertTrue($actual);
        $this->assertFileDoesNotExist("vfs://test/coco/test.htm");
    }

    public function testDeletesBackup(): void
    {
        vfsStream::setup("test");
        mkdir("vfs://test/coco/");
        touch("vfs://test/coco/20230306_120000_test.htm");
        $sut = new Repository("vfs://test/coco/", "", $this->pages(), $this->idGenerator());
        $actual = $sut->delete("test", "20230306_120000");
        $this->assertTrue($actual);
        $this->assertFileDoesNotExist("vfs://test/coco/20230306_120000_test.htm");
    }

    private function pages(bool $new = false): Pages
    {
        $pages = $this->createMock(Pages::class);
        $pages->method("count")->willReturn(2);
        $pages->method("level")->willReturnMap([[0, 1], [1, 2]]);
        $pages->method("heading")->willReturnMap([[0, "Start"], [1, "Sub"]]);
        if ($new) {
            $pages->method("data")->willReturnOnConsecutiveCalls([], [], ["coco_id" => "12345"], ["coco_id" => "23456"]);
        } else {
            $pages->method("data")->willReturnMap([[0, ["coco_id" => "12345"]], [1, ["coco_id" => "23456"]]]);
        }
        return $pages;
    }

    private function idGenerator(): IdGenerator
    {
        $idGenerator = $this->createMock(IdGenerator::class);
        $idGenerator->method("newId")->willReturnOnConsecutiveCalls("12345", "23456");
        return $idGenerator;
    }
}
