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

namespace Coco;

use ApprovalTests\Approvals;
use Coco\Infra\Pages;
use Coco\Infra\Repository;
use Coco\Infra\RequestStub;
use Coco\Infra\XhStuff;
use PHPUnit\Framework\TestCase;
use Plib\View;

class SearchTest extends TestCase
{
    public function testRendersSearchResults(): void
    {
        $sut = $this->sut();
        $request = new RequestStub(["query" => "search=regular"]);
        $response = $sut($request);
        $this->assertEquals("Search Results", $response->title());
        Approvals::verifyHtml($response->output());
    }

    private function sut(): Search
    {
        return new Search(
            $this->repository(),
            $this->pages(),
            $this->xhStuff(),
            $this->view()
        );
    }

    private function repository(): Repository
    {
        $repository = $this->createStub(Repository::class);
        $repository->method("findAllNames")->willReturn(["foo", "bar"]);
        $repository->method("findAll")->willReturn(["<p>some other co-content</p>", "<p>some regular co-content</p>"]);
        return $repository;
    }

    private function pages(): Pages
    {
        $pages = $this->createStub(Pages::class);
        $pages->method("url")->willReturnMap([[0, "Welcome"], [1, "Cocos"]]);
        $pages->method("heading")->willReturnMap([[0, "Welcome!"], [1, "Cocos"]]);
        return $pages;
    }

    private function xhStuff(): XhStuff
    {
        $xhStuff = $this->createStub(XhStuff::class);
        $xhStuff->method("evaluateScripting")->willReturnArgument(0);
        return $xhStuff;
    }

    private function view(): View
    {
        return new View("./views/", XH_includeVar("./languages/en.php", "plugin_tx")["coco"]);
    }
}
