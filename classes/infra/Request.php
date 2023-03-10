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

class Request
{
    /** @codeCoverageIgnore */
    public static function current(): self
    {
        return new Request;
    }

    /** @codeCoverageIgnore */
    public function sn(): string
    {
        global $sn;
        return $sn;
    }

    /** @codeCoverageIgnore */
    public function search(): string
    {
        global $search;
        return $search;
    }

    /** @codeCoverageIgnore */
    public function adm(): bool
    {
        return defined("XH_ADM") && XH_ADM;
    }

    /** @codeCoverageIgnore */
    public function edit(): bool
    {
        global $edit;
        return $edit;
    }

    /** @codeCoverageIgnore */
    public function s(): int
    {
        global $s;
        return $s;
    }

    /** @codeCoverageIgnore */
    public function action(): string
    {
        global $action;
        return $action;
    }

    /** @codeCoverageIgnore */
    public function server(string $key): ?string
    {
        return $_SERVER[$key] ?? null;
    }

    /** @codeCoverageIgnore */
    public function queryString(): string
    {
        return $_SERVER["QUERY_STRING"];
    }

    /** @return list<string>|null */
    public function cocoNames(): ?array
    {
        $get = $this->get();
        if (!isset($get["coco_name"]) || !is_array($get["coco_name"])) {
            return null;
        }
        return array_values($get["coco_name"]);
    }

    /**
     * @return array<string,string|array<string>>
     * @codeCoverageIgnore
     */
    protected function get(): array
    {
        return $_GET;
    }

    /** @return string|null */
    public function cocoText(string $name): ?string
    {
        $post = $this->post();
        if (!isset($post["coco_text_$name"]) || !is_string($post["coco_text_$name"])) {
            return null;
        }
        return $post["coco_text_$name"];
    }

    /**
     * @return array<string,string|array<string>>
     * @codeCoverageIgnore
     */
    protected function post(): array
    {
        return $_POST;
    }

    /** @codeCoverageIgnore */
    public function logOut(): bool
    {
        global $f;
        return $f === "xh_loggedout";
    }
}
