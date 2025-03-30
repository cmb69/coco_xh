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
use Coco\Infra\Repository;
use PHPUnit\Framework\TestCase;
use Plib\FakeSystemChecker;
use Plib\View;

class PluginInfoTest extends TestCase
{
    public function testRendersPluginInfo(): void
    {
        $sut = $this->sut();
        $response = $sut();
        $this->assertEquals("Coco 2.0-dev", $response->title());
        Approvals::verifyHtml($response->output());
    }

    private function sut(): PluginInfo
    {
        $repository = $this->createStub(Repository::class);
        $repository->method("dataFolder")->willReturn("./content/coco/");
        $text = XH_includeVar("./languages/en.php", "plugin_tx")["coco"];
        return new PluginInfo(
            "./plugins/coco/",
            $repository,
            new FakeSystemChecker(),
            new View("./views/", $text)
        );
    }
}
