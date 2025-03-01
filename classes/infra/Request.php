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

use Coco\Value\Url;

class Request
{
    public static function current(): self
    {
        return new self();
    }

    public function url(): Url
    {
        $rest = $this->query();
        if ($rest !== "") {
            $rest = "?" . $rest;
        }
        return Url::from(CMSIMPLE_URL . $rest);
    }

    public function search(): string
    {
        $search = $this->url()->param("search");
        if (!is_string($search)) {
            return "";
        }
        return $search;
    }

    protected function edit(): bool
    {
        global $edit;
        return defined("XH_ADM") && XH_ADM && $edit;
    }

    public function s(): int
    {
        global $s;
        return $s;
    }

    public function time(): int
    {
        return (int) $_SERVER["REQUEST_TIME"];
    }

    protected function query(): string
    {
        return $_SERVER["QUERY_STRING"];
    }

    public function cocoAction(string $coconame): string
    {
        if (!$this->edit()) {
            return "";
        }
        if (!$this->hasCocoText($coconame)) {
            return "edit";
        }
        return "do_edit";
    }

    public function cocoAdminAction(): string
    {
        $action = $this->url()->param("action");
        $post = $this->post();
        if ($action && isset($post["coco_do"]) && $action === $post["coco_do"] && $this->hasCocoNames()) {
            return "do_delete";
        }
        if ($action === "delete" && $this->hasCocoNames()) {
            return "delete";
        }
        return "";
    }

    /** @return list<string> */
    public function cocoNames(): array
    {
        assert($this->hasCocoNames());
        $names = $this->url()->param("coco_name");
        assert(is_array($names));
        return array_values($names);
    }

    private function hasCocoNames(): bool
    {
        $names = $this->url()->param("coco_name");
        return is_array($names);
    }

    public function cocoText(string $name): string
    {
        assert($this->hasCocoText($name));
        $post = $this->post();
        assert(isset($post["coco_text_$name"]) && is_string($post["coco_text_$name"]));
        return $post["coco_text_$name"];
    }

    private function hasCocoText(string $name): bool
    {
        $post = $this->post();
        if (!isset($post["coco_text_$name"]) || !is_string($post["coco_text_$name"])) {
            return false;
        }
        return true;
    }

    /** @return array<string,string|array<string>> */
    protected function post(): array
    {
        return $_POST;
    }

    public function logOut(): bool
    {
        global $f;
        return $f === "xh_loggedout";
    }
}
