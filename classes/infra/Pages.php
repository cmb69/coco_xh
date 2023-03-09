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

use XH\PageDataRouter;
use XH\Pages as XhPages;

class Pages
{
    /** @var XhPages */
    protected $xhPages;

    /** @var PageDataRouter */
    protected $pageDataRouter;

    public function __construct(?XhPages $xhPages = null)
    {
        global $pd_router;
        $this->xhPages = $xhPages ?? new XhPages;
        $this->pageDataRouter = $pd_router;
    }

    /**
     * @return array<string,string>
     * @codeCoverageIgnore
     */
    public function data(int $pageIndex): array
    {
        return $this->pageDataRouter->find_page($pageIndex);
    }

    /**
     * @param array<string,string> $pageData
     * @return void
     * @codeCoverageIgnore
     */
    public function updateData(int $pageIndex, array $pageData)
    {
        $this->pageDataRouter->update($pageIndex, $pageData);
    }

    /** @codeCoverageIgnore */
    public function count(): int
    {
        return $this->xhPages->getCount();
    }

    /** @codeCoverageIgnore */
    public function isHidden(int $pageIndex): bool
    {
        return $this->xhPages->isHidden($pageIndex);
    }

    /** @codeCoverageIgnore */
    public function level(int $pageIndex): int
    {
        return $this->xhPages->level($pageIndex);
    }

    /** @codeCoverageIgnore */
    public function heading(int $pageIndex): string
    {
        return $this->xhPages->heading($pageIndex);
    }

    /** @codeCoverageIgnore */
    public function url(int $pageIndex): string
    {
        return $this->xhPages->url($pageIndex);
    }

    /** @return list<string> */
    public function contents(): array
    {
        $result = [];
        for ($i = 0; $i < $this->xhPages->getCount(); $i++) {
            $result[] = $this->xhPages->content($i);
        }
        return $result;
    }
}
