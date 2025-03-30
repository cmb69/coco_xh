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
use PHPUnit\Framework\TestCase;
use Plib\Random;

class RepositoryTest extends TestCase
{
    public function testCreatesDataDirOnAccess(): void
    {
        vfsStream::setup("root");
        $sut = $this->sut();
        $this->assertEquals("vfs://root/coco/", $sut->dataFolder());
        $this->assertDirectoryExists("vfs://root/coco/");
    }

    public function testFilenameIsCorrect(): void
    {
        vfsStream::setup("root");
        $sut = $this->sut();
        $this->assertSame("vfs://root/coco/foo.htm", $sut->filename("foo"));
    }

    public function testBackupFilenameIsCorrect(): void
    {
        vfsStream::setup("root");
        $sut = $this->sut();
        $filename = $sut->filename("test", "20230309_224602");
        $this->assertEquals("vfs://root/coco/20230309_224602_test.htm", $filename);
    }

    public function testSavesCoco(): void
    {
        vfsStream::setup("root");
        $sut = $this->sut(["pages" => $this->pages(true)]);
        $sut->save("foo", 0, "<p>some content</p>");
        $sut->save("foo", 1, "<p>other content</p>");
        $this->assertStringEqualsFile("vfs://root/coco/foo.htm", $this->coco());
    }

    public function testCanSaveEmptyContent(): void
    {
        vfsStream::setup("root");
        $sut = $this->sut(["pages" => $this->pages(true)]);
        $sut->save("foo", 0, "");
        $this->assertStringEqualsFile("vfs://root/coco/foo.htm", $this->emptyCoco());
    }

    public function testSavingFailsIfFileIsNotWritable(): void
    {
        vfsStream::setup("root", null, ["coco" => ["foo.htm" => []]]);
        $sut = $this->sut(["pages" => $this->pages(true)]);
        $this->expectException(RepositoryException::class);
        $sut->save("foo", 0, "<p>some content</p>");
    }

    public function testFindsAllNames(): void
    {
        vfsStream::setup("root", null, ["coco" => ["foo.htm" => "", "bar.htm" => ""]]);
        $sut = $this->sut();
        $names = $sut->findAllNames();
        $this->assertSame(["bar", "foo"], $names);
    }

    public function testFindsAllCoContents(): void
    {
        vfsStream::setup("root", null, ["coco" => ["foo.htm" => $this->coco()]]);
        $sut = $this->sut();
        $result = $sut->findAll("foo");
        $this->assertSame(["<p>some content</p>", "<p>other content</p>"], iterator_to_array($result));
    }

    public function testFindsEmptyCoContensIfIdsAreMissing(): void
    {
        vfsStream::setup("root", null, ["coco" => ["foo.htm" => $this->coco()]]);
        $sut = $this->sut(["pages" => $this->pages(true)]);
        $result = $sut->findAll("foo");
        $this->assertSame(["", ""], iterator_to_array($result));
    }

    public function testFindsEmptyArrayIfCocoFileDoesNotExist(): void
    {
        vfsStream::setup("root");
        $sut = $this->sut();
        $result = $sut->findAll("foo");
        $this->assertEquals([], iterator_to_array($result));
    }

    public function testFindsAllBackups(): void
    {
        vfsStream::setup("root", null, ["coco" => [
            "20230309_120000_foo.htm" => "",
            "20230306_120000_test.htm" => "",
            "20230307_120000_test.htm" => "",
            "20230309_120000_test.htm" => "",
            "20230309-120000_test.htm" => "",
        ]]);
        $expected = [
            ["test", "20230306_120000"],
            ["test", "20230307_120000"],
            ["test", "20230309_120000"],
        ];
        $sut = $this->sut();
        $actual = $sut->findAllBackups("test");
        $this->assertEquals($expected, $actual);
    }

    public function testFindsCoContent(): void
    {
        vfsStream::setup("root", null, ["coco" => ["foo.htm" => $this->coco()]]);
        $sut = $this->sut();
        $result = $sut->find("foo", 0);
        $this->assertSame("<p>some content</p>", $result);
    }

    public function testFindsEmptyCoContentIfIdIsMissing(): void
    {
        vfsStream::setup("root", null, ["coco" => ["foo.htm" => $this->coco()]]);
        $sut = $this->sut(["pages" => $this->pages(true)]);
        $result = $sut->find("foo", 0);
        $this->assertSame("", $result);
    }

    public function testFindsEmptyStringIfCocoFileDoesNotExist(): void
    {
        vfsStream::setup("root");
        $sut = $this->sut();
        $result = $sut->find("foo", 0);
        $this->assertEquals("", $result);
    }

    public function testCreatesBackup(): void
    {
        vfsStream::setup("root", null, ["coco" => ["test.htm" => ""]]);
        $sut = $this->sut();
        $sut->backup("test", "20230309_224602");
        $this->assertFileExists("vfs://root/coco/20230309_224602_test.htm");
    }

    public function testDeletesCoContents(): void
    {
        vfsStream::setup("root", null, ["coco" => ["test.htm" => ""]]);
        $sut = $this->sut();
        $sut->delete("test");
        $this->assertFileDoesNotExist("vfs://root/coco/test.htm");
    }

    public function testDeletesBackup(): void
    {
        vfsStream::setup("root", "", ["coco" => ["20230306_120000_test.htm" => ""]]);
        $sut = $this->sut();
        $sut->delete("test", "20230306_120000");
        $this->assertFileDoesNotExist("vfs://root/coco/20230306_120000_test.htm");
    }

    private function sut(array $deps = []): Repository
    {
        $random = $this->createStub(Random::class);
        $random->method("bytes")->willReturnOnConsecutiveCalls("0123456789ABCDEF", "123456789ABCDEF0");
        return new Repository(
            "vfs://root/coco/",
            "",
            $deps["pages"] ?? $this->pages(),
            $random
        );
    }

    private function pages(bool $new = false): Pages
    {
        $pages = $this->createMock(Pages::class);
        $pages->method("count")->willReturn(2);
        $pages->method("level")->willReturnMap([[0, 1], [1, 2]]);
        $pages->method("heading")->willReturnMap([[0, "Start"], [1, "Sub"]]);
        if ($new) {
            $pages->method("data")->willReturnOnConsecutiveCalls([], [], ["coco_id" => "30313233-3435-4637-B839-414243444546"], ["coco_id" => "31323334-3536-4738-B941-424344454630"]);
        } else {
            $pages->method("data")->willReturnMap([[0, ["coco_id" => "30313233-3435-4637-B839-414243444546"]], [1, ["coco_id" => "31323334-3536-4738-B941-424344454630"]]]);
        }
        return $pages;
    }

    private function coco(): string
    {
        return <<<'HTML'
        <html>
        <body>
        <h1 id="30313233-3435-4637-B839-414243444546">Start</h1>
        <p>some content</p>
        <h2 id="31323334-3536-4738-B941-424344454630">Sub</h2>
        <p>other content</p>
        </body>
        </html>

        HTML;
    }

    private function emptyCoco(): string
    {
        return <<<'HTML'
        <html>
        <body>
        <h1 id="30313233-3435-4637-B839-414243444546">Start</h1>
        </body>
        </html>

        HTML;
    }
}
