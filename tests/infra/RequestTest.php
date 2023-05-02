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
    /** @dataProvider cocoActions */
    public function testCocoAction(string $coconame, bool $edit, array $post, string $expected): void
    {
        $sut = new RequestStub(["edit" => $edit, "post" => $post]);
        $action = $sut->cocoAction($coconame);
        $this->assertEquals($expected, $action);
    }

    public function cocoActions(): array
    {
        return [
            ["test", false, [], ""],
            ["test", true, [], "edit"],
            ["test", true, ["coco_text_test" => ""], "do_edit"],
        ];
    }

    /** @dataProvider cocoAdminActions */
    public function testCocoAdminAction(string $query, array $post, string $expected): void
    {
        $sut = new RequestStub(["query" => $query, "post" => $post]);
        $action = $sut->cocoAdminAction();
        $this->assertEquals($expected, $action);
    }

    public function cocoAdminActions(): array
    {
        return [
            ["", [], ""],
            ["coco_name[]=foo&action=delete", [], "delete"],
            ["action=delete", [], ""],
            ["coco_name[]=foo&action=delete", ["coco_do" => "delete"], "do_delete"],
            ["action=delete", ["coco_do" => "delete"], ""],
            ["coco_name[]=foo", [], ""],
        ];
    }

    public function testCocoNames(): void
    {
        $sut = new RequestStub(["query" => "coco_name[]=foo&coco_name[]=bar"]);
        $names = $sut->cocoNames();
        $this->assertSame(["foo", "bar"], $names);
    }

    public function testCocoText(): void
    {
        $sut = new RequestStub(["post" => ["coco_text_foo" => "some text"]]);
        $text = $sut->cocoText("foo");
        $this->assertSame("some text", $text);
    }
}
