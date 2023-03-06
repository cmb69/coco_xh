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

class FakeCocoService extends CocoService
{
    private $options;

    public function __construct($options = [])
    {
        $this->options = $options;
    }

    public function dataDir()
    {
        return "./content/coco";
    }

    public function findAllNames()
    {
        return ["foo", "bar"];
    }

    public function find($name, $i)
    {
        return "<p>some HTML with {{{trim('scripting')}}}</p>";
    }

    public function findAll($name)
    {
        return [
            "<p>some other co-content</p>",
            "<p>some regular co-content</p>",
        ];
    }

    public function save($name, $i, $text)
    {
        return $this->options["save"] ?? true;
    }

    public function delete($name)
    {
        return [
            "./content/coco/20230306_120000_$name.htm" => false,
            "./content/coco/$name.htm" => false,
        ];
    }
}
