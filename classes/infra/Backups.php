<?php

/**
 * Copyright 2012-2023 Christoph M. Becker
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

class Backups
{
    public function filename(string $foldername, string $coconame, string $date): string
    {
        return $foldername . $date . "_" . $coconame . ".htm";
    }

    public function create(string $foldername, string $coconame, string $date): bool
    {
        return copy($foldername . $coconame . ".htm", $this->filename($foldername, $coconame, $date));
    }

    /** @return list<string> */
    public function all(string $foldername, string $coconame): array
    {
        $pattern = sprintf('/^\d{8}_\d{6}_%s\.htm$/', preg_quote($coconame, "/"));
        $result = [];
        if (($dir = opendir($foldername)) !== false) {
            while (($entry = readdir($dir)) !== false) {
                if (preg_match($pattern, $entry)) {
                    $result[] = $foldername . $entry;
                }
            }
            closedir($dir);
        }
        return $result;
    }

    public function delete(string $filename): bool
    {
        return unlink($filename);
    }
}
