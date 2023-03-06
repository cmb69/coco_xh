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

class FakeBackups extends Backups
{
    private $options;

    public function __construct($options = [])
    {
        $this->options = $options;
    }

    public function create(string $foldername, string $coconame, string $date): bool
    {
        return $this->options["create"] ?? true;
    }

    public function all(string $foldername, string $coconame): array
    {
        return [
            $foldername . "20230304_120000_" . $coconame . ".htm",
            $foldername . "20230305_120000_" . $coconame . ".htm",
            $foldername . "20230306_120000_" . $coconame . ".htm",
        ];
    }

    public function delete(string $filename): bool
    {
        return $this->options["delete"] ?? true;
    }
}
