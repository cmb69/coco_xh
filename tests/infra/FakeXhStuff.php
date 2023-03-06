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

class FakeXhStuff extends XhStuff
{
    public function evaluateScripting(string $text): string
    {
        return preg_replace_callback(
            '/{{{([^}]+)}}}/',
             function (array $matches) {
                return eval("return $matches[1];");
             },
             $text
        );
    }

    public function highlightSearchWords(array $words, string $text): string
    {
        return str_replace("with", "<span class=\"highlight\">with</span>", $text);
    }

    public function replaceEditor(string $id, string $init)
    {
        return "tinymce.init('$id');";
    }

    public function hide(int $pageIndex): bool
    {
        return false;
    }

    public function h(): array
    {
        return ["Welcome!", "Cocos"];
    }

    public function u(): array
    {
        return ["Welcome", "Cocos"];
    }

    public function c(): array
    {
        return ["<p>some page content</p>", "<p>other content</p>"];
    }
}
