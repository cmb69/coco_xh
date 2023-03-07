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
        $this->assertEquals("Coco – Co-Contents", $response->title());
        Approvals::verifyHtml($response->output());
    }

    public function testRendersDeleteConfirmation(): void
    {
        $_GET = ["coco_name" => ["foo"]];
        $sut = $this->sut();
        $response = $sut(new FakeRequest(["action" => "delete"]));
        $this->assertEquals("Coco – Co-Contents", $response->title());
        Approvals::verifyHtml($response->output());
    }

    public function testDeletionIsCsrfProtected(): void
    {
        $sut = $this->sut(["csrf" => ["check" => true]]);
        $this->expectExceptionMessage("CSRF check failed!");
        $sut(new FakeRequest(["action" => "do_delete"]));
    }

    public function testSuccessfulDeletionRedirects(): void
    {
        $_POST = ["coco_name" => ["foo"]];
        $sut = $this->sut();
        $response = $sut(new FakeRequest(["action" => "do_delete"]));
        $this->assertEquals("http://example.com/?coco&admin=plugin_main", $response->location());
    }

    public function testFailureToDeleteIsReported(): void
    {
        $_POST = ["coco_name" => ["foo"]];
        $sut = $this->sut(["service" => ["delete" => [
            "./content/coco/20230306_120000_foo.htm",
            "./content/coco/foo.htm",
        ]]]);
        $response = $sut(new FakeRequest(["action" => "do_delete"]));
        Approvals::verifyHtml($response->output());
    }

    private function sut($options = []): MainAdminController
    {
        return new MainAdminController(
            new FakeCocoService($options["service"] ?? []),
            new FakeCsrfProtector($options["csrf"] ?? []),
            new View("./views/", XH_includeVar("./languages/en.php", "plugin_tx")["coco"])
        );
    }
}
