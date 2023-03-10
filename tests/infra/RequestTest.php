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

use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    /** @dataProvider cocoNamesData */
    public function testCocoNames(array $get, ?array $expected): void
    {
        $sut = $this->sut();
        $sut->method("get")->willReturn($get);
        $names = $sut->cocoNames();
        $this->assertSame($expected, $names);
    }

    public function cocoNamesData(): array
    {
        return [
            [[], null],
            [["coco_name" => "foo"], null],
            [["coco_name" => ["foo", "bar"]], ["foo", "bar"]],
        ];
    }

    /** @dataProvider cocoTexts */
    public function testCocoText(array $post, ?string $expected): void
    {
        $sut = $this->sut();
        $sut->method("post")->willReturn($post);
        $text = $sut->cocoText("foo");
        $this->assertSame($expected, $text);
    }

    public function cocoTexts(): array
    {
        return [
            [[], null],
            [["coco_text_foo" => []], null],
            [["coco_text_foo" => "some text"], "some text"],
            [["coco_text_bar" => "some text"], null],
        ];
    }

    private function sut(): Request
    {
        return $this->getMockBuilder(Request::class)
        ->disableOriginalConstructor()
        ->disableOriginalClone()
        ->disableArgumentCloning()
        ->disallowMockingUnknownTypes()
        ->onlyMethods(["get", "post"])
        ->getMock();
    }
}
