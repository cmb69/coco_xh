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
use Coco\Infra\FakeCsrfProtector;
use Coco\Infra\FakeRequest;
use Coco\Infra\View;
use PHPUnit\Framework\TestCase;

class MainAdminControllerTest extends TestCase
{
    public function testRendersCocoOverview(): void
    {
        $sut = $this->sut();
        $response = $sut(new FakeRequest(["action" => ""]));
        Approvals::verifyHtml($response);
    }

    public function testDeletionIsCsrfProtected(): void
    {
        $sut = $this->sut(["csrf" => ["check" => true]]);
        $this->expectExceptionMessage("CSRF check failed!");
        $sut(new FakeRequest(["action" => "delete"]));
    }

    public function testFailureToDeleteIsReported(): void
    {
        $_POST = ["coco_name" => "foo"];
        $sut = $this->sut();
        $response = $sut(new FakeRequest(["action" => "delete"]));
        Approvals::verifyHtml($response);
    }

    private function sut($options = []): MainAdminController
    {
        return new MainAdminController(
            new FakeCocoService,
            new FakeCsrfProtector($options["csrf"] ?? []),
            new View("./views/", XH_includeVar("./languages/en.php", "plugin_tx")["coco"])
        );
    }
}
