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
use Coco\Infra\Request;
use Coco\Infra\View;
use Coco\Infra\XhStuff;
use PHPUnit\Framework\TestCase;

class SearchTest extends TestCase
{
    public function testRendersSearchResultsWithTwoHits(): void
    {
        $sut = new Search($this->repository(), $this->pages(), $this->xhStuff(), $this->view());
        $response = $sut($this->request("some"));
        $this->assertEquals("Search Results", $response->title());
        Approvals::verifyHtml($response->output());
    }

    public function testRendersSearchResultsWithOneHit(): void
    {
        $sut = new Search($this->repository(), $this->pages(), $this->xhStuff(), $this->view());
        $response = $sut($this->request("regular"));
        $this->assertEquals("Search Results", $response->title());
        Approvals::verifyHtml($response->output());
    }

    public function testRendersSearchResultsWithoutHit(): void
    {
        $sut = new Search($this->repository(), $this->pages(), $this->xhStuff(), $this->view());
        $response = $sut($this->request("doesnotexist"));
        $this->assertEquals("Search Results", $response->title());
        Approvals::verifyHtml($response->output());
    }

    private function request(string $search): Request
    {
        $request = $this->createStub(Request::class);
        $request->method("sn")->willReturn("/");
        $request->method("search")->willReturn($search);
        return $request;
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
        $xhStuff->method("evaluateScripting")->willReturnOnConsecutiveCalls(
            "<p>some page content</p>", "<p>other content</p>",
            "<p>some other co-content</p>", "<p>some regular co-content</p>",
            "<p>some other co-content</p>", "<p>some regular co-content</p>"
        );
        return $xhStuff;
    }

    private function view(): View
    {
        return new View("./views/", XH_includeVar("./languages/en.php", "plugin_tx")["coco"]);
    }
}
