<?php

/**
 * Copyright 2023 Christoph M. Becker
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

class BackupsTest extends TestCase
{
    public function testFilename(): void
    {
        vfsStream::setup("root");
        $sut = new Backups;
        $filename = $sut->filename("vfs://root/", "test", "20230309_224602");
        $this->assertEquals("vfs://root/20230309_224602_test.htm", $filename);
    }

    public function testCreate(): void
    {
        vfsStream::setup("root");
        touch("vfs://root/test.htm");
        $sut = new Backups;
        $result = $sut->create("vfs://root/", "test", "20230309_224602");
        $this->assertTrue($result);
        $this->assertFileExists("vfs://root/20230309_224602_test.htm");
    }

    public function testAll(): void
    {
        vfsStream::setup("root");
        touch("vfs://root/20230309_120000_foo.htm");
        touch("vfs://root/20230306_120000_test.htm");
        touch("vfs://root/20230307_120000_test.htm");
        touch("vfs://root/20230309_120000_test.htm");
        touch("vfs://root/20230309-120000_test.htm");
        $expected = [
            "vfs://root/20230306_120000_test.htm",
            "vfs://root/20230307_120000_test.htm",
            "vfs://root/20230309_120000_test.htm"
        ];
        $sut = new Backups;
        $actual = $sut->all("vfs://root/", "test");
        $this->assertEquals($expected, $actual);
    }

    public function testDelete(): void
    {
        vfsStream::setup("root");
        touch("vfs://root/20230306_120000_test.htm");
        $sut = new Backups;
        $actual = $sut->delete("vfs://root/20230306_120000_test.htm");
        $this->assertTrue($actual);
        $this->assertFileDoesNotExist("vfs://root/20230306_120000_test.htm");
    }
}
