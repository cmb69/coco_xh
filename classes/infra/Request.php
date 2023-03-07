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

/** @codeCoverageIgnore */
class Request
{
    public static function current(): self
    {
        return new Request;
    }

    public function sn(): string
    {
        global $sn;
        return $sn;
    }

    public function search(): string
    {
        global $search;
        return $search;
    }

    public function adm(): bool
    {
        return defined("XH_ADM") && XH_ADM;
    }

    public function edit(): bool
    {
        global $edit;
        return $edit;
    }

    public function s(): int
    {
        global $s;
        return $s;
    }

    public function action(): string
    {
        global $action;
        return $action;
    }

    public function server(string $key): ?string
    {
        return $_SERVER[$key] ?? null;
    }

    public function queryString(): string
    {
        return $_SERVER["QUERY_STRING"];
    }

    public function forms(): Forms
    {
        return new Forms;
    }
}
