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
use Coco\Infra\FakeXhStuff;
use PHPUnit\Framework\TestCase;
use Plib\HtmlView as View;

class SearchTest extends TestCase
{
    public function testRendersSearchResultsWithTwoHits(): void
    {
        $sut = $this->sut();
        $response = $sut("/", "some");
        Approvals::verifyHtml($response);
    }

    public function testRendersSearchResultsWithOneHit(): void
    {
        $sut = $this->sut();
        $response = $sut("/", "regular");
        Approvals::verifyHtml($response);
    }

    public function testRendersSearchResultsWithoutHit(): void
    {
        $sut = $this->sut();
        $response = $sut("/", "doesnotexist");
        Approvals::verifyHtml($response);
    }

    private function sut(): Search
    {
        return new Search(
            new FakeCocoService(),
            new FakeXhStuff,
            new View("./views/", XH_includeVar("./languages/en.php", "plugin_tx")["coco"])
        );

    }
}
