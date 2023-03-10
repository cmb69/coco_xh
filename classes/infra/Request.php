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
    protected function editMode(): bool
    {
        global $edit;
        return defined("XH_ADM") && XH_ADM && $edit;
    }

    /** @codeCoverageIgnore */
    public function s(): int
    {
        global $s;
        return $s;
    }

    /** @codeCoverageIgnore */
    public function requestTime(): int
    {
        return (int) $_SERVER["REQUEST_TIME"];
    }

    /** @codeCoverageIgnore */
    public function queryString(): string
    {
        return $_SERVER["QUERY_STRING"];
    }

    public function cocoAction(string $coconame): string
    {
        if (!$this->editMode()) {
            return "";
        }
        if (!$this->hasCocoText($coconame)) {
            return "edit";
        }
        return "do_edit";
    }

    public function cocoAdminAction(): string
    {
        $post = $this->post();
        if (($post["coco_do"] ?? null) === $this->action() && $this->hasCocoNames()) {
            return "do_delete";
        }
        if ($this->action() === "delete" && $this->hasCocoNames()) {
            return "delete";
        }
        return "";
    }

    /** @codeCoverageIgnore */
    protected function action(): string
    {
        global $action;
        return $action;
    }

    /** @return list<string> */
    public function cocoNames(): array
    {
        assert($this->hasCocoNames());
        $get = $this->get();
        assert(isset($get["coco_name"]) && is_array($get["coco_name"]));
        return array_values($get["coco_name"]);
    }

    private function hasCocoNames(): bool
    {
        $get = $this->get();
        if (!isset($get["coco_name"]) || !is_array($get["coco_name"]) || empty($get["coco_name"])) {
            return false;
        }
        return true;
    }

    /**
     * @return array<string,string|array<string>>
     * @codeCoverageIgnore
     */
    protected function get(): array
    {
        return $_GET;
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
