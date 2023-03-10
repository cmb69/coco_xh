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
use Coco\Infra\SystemChecker;
use Coco\Infra\View;
use PHPUnit\Framework\TestCase;

class PluginInfoTest extends TestCase
{
    public function testRendersPluginInfo(): void
    {
        $repository = $this->createStub(Repository::class);
        $repository->method("dataFolder")->willReturn("./content/coco/");
        $systemChecker = $this->createStub(SystemChecker::class);
        $systemChecker->method("checkVersion")->willReturn(false);
        $systemChecker->method("checkWritability")->willReturn(false);
        $text = XH_includeVar("./languages/en.php", "plugin_tx")["coco"];
        $sut = new PluginInfo(
            "./plugins/coco/",
            $repository,
            $systemChecker,
            new View("./views/", $text)
        );
        $response = $sut();
        $this->assertEquals("Coco 2.0-dev", $response->title());
        Approvals::verifyHtml($response->output());
    }
}
