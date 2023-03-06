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
use Coco\Infra\FakeCocoService;
use Coco\Infra\FakePages;
use Coco\Infra\FakeRequest;
use Coco\Infra\FakeXhStuff;
use PHPUnit\Framework\TestCase;
use Plib\HtmlView as View;

class SearchTest extends TestCase
{
    public function testRendersSearchResultsWithTwoHits(): void
    {
        $sut = $this->sut();
        $response = $sut(new FakeRequest(["search" => "some"]));
        $this->assertEquals("Search Results", $response->title());
        Approvals::verifyHtml($response->output());
    }

    public function testRendersSearchResultsWithOneHit(): void
    {
        $sut = $this->sut();
        $response = $sut(new FakeRequest(["search" => "regular"]));
        $this->assertEquals("Search Results", $response->title());
        Approvals::verifyHtml($response->output());
    }

    public function testRendersSearchResultsWithoutHit(): void
    {
        $sut = $this->sut();
        $response = $sut(new FakeRequest(["search" => "doesnotexist"]));
        $this->assertEquals("Search Results", $response->title());
        Approvals::verifyHtml($response->output());
    }

    private function sut(): Search
    {
        return new Search(
            new FakeCocoService(),
            new FakePages(["headings" => ["Welcome!", "Cocos"], "url" => ["Welcome", "Cocos"], "count" => 2]),
            new FakeXhStuff,
            new View("./views/", XH_includeVar("./languages/en.php", "plugin_tx")["coco"])
        );

    }
}
