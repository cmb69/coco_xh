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

class FakePages extends Pages
{
    public function __construct($options = [])
    {
        $this->xhPages = new FakeXhPages($options);
        $this->pageDataRouter = new FakePageDataRouter($options);
    }
}

class FakeXhPages
{
    private $options;

    public function __construct($options)
    {
        $this->options = $options;
    }

    public function getCount(): int
    {
        return $this->options["count"] ?? 0;
    }

    public function level(int $pageIndex): int
    {
        return $this->options["levels"][$pageIndex] ?? 1;
    }

    public function heading(int $pageIndex): string
    {
        return $this->options["headings"][$pageIndex] ?? "";
    }

    public function url(int $pageIndex): string
    {
        return $this->options["url"][$pageIndex] ?? "";
    }

    public function isHidden(int $pageIndex): bool
    {
        return false;
    }

    public function content(): string
    {
        $c = ["<p>some page content</p>", "<p>other content</p>"];
        $res = current($c);
        next($c);
        return $res;
    }
}

class FakePageDataRouter
{
    private $options;

    public function __construct($options)
    {
        $this->options = $options;
    }

    public function find_page(int $pageIndex): array
    {
        if (isset($this->options["data"])) {
            $res = current($this->options["data"]);
            next($this->options["data"]);
            return $res;
        }
        return [];
    }

    public function update(int $pageIndex, array $pageData) {}
}
