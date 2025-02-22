<?php

/**
 * Copyright 2025 Christoph M. Becker
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

use PHPUnit\Framework\TestCase;

class IdGeneratorTest extends TestCase
{
    public function testIds(): void
    {
        $sut = $this->sut();
        $this->assertEquals("2A0AB9C0-1F3A-4EFA-8827-A69AE54E0B88", $sut->newId());
        $this->assertEquals("5B281321-07F2-441F-8ED5-090ECD0AA781", $sut->newId());
    }

    private function sut(): IdGenerator
    {
        return new class() extends IdGenerator {
            private $ids = [
                "\x2a\x0a\xb9\xc0\x1f\x3a\x1e\xfa\x48\x27\xa6\x9a\xe5\x4e\x0b\x88",
                "\x5b\x28\x13\x21\x07\xf2\x14\x1f\x8e\xd5\x09\x0e\xcd\x0a\xa7\x81"
            ];
            protected function randomBytes(): string
            {
                $id = current($this->ids);
                next($this->ids);
                return $id; 
            }
        };
    }
}
