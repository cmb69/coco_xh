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

class IdGenerator
{
    public function newId(): string
    {
        $rand = $this->randomBytes();
        $rand[6] = chr(ord($rand[6]) & 0x0f | 0x40);
        $rand[8] = chr(ord($rand[8]) & 0x3f | 0x80);
        $uuid = strtoupper(bin2hex($rand));
        return substr($uuid, 0, 8) . "-" . substr($uuid, 8, 4) . "-"
            . substr($uuid, 12, 4) . "-" . substr($uuid, 16, 4) . "-"
            . substr($uuid, 20, 12);
    }

    protected function randomBytes(): string
    {
        return random_bytes(16);
    }
}
