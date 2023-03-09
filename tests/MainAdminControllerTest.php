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
use Coco\Infra\CocoService;
use Coco\Infra\CsrfProtector;
use Coco\Infra\Forms;
use Coco\Infra\Request;
use Coco\Infra\View;
use PHPUnit\Framework\TestCase;

class MainAdminControllerTest extends TestCase
{
    public function testRendersCocoOverview(): void
    {
        $sut = new MainAdminController($this->cocoService(), $this->csrfProtector(), $this->view());
        $response = $sut($this->request());
        $this->assertEquals("Coco – Co-Contents", $response->title());
        Approvals::verifyHtml($response->output());
    }

    public function testRendersDeleteConfirmation(): void
    {
        $_GET = ["coco_name" => ["foo"]];
        $sut = new MainAdminController($this->cocoService(), $this->csrfProtector(), $this->view());
        $response = $sut($this->request("delete"));
        $this->assertEquals("Coco – Co-Contents", $response->title());
        Approvals::verifyHtml($response->output());
    }

    public function testSuccessfulDeletionRedirects(): void
    {
        $_GET = ["coco_name" => ["foo"]];
        $_POST = ["coco_do" => "delete"];
        $sut = new MainAdminController($this->cocoService(), $this->csrfProtector(true), $this->view());
        $response = $sut($this->request("delete"));
        $this->assertEquals("http://example.com/?coco&admin=plugin_main", $response->location());
    }

    public function testFailureToDeleteIsReported(): void
    {
        $_GET = ["coco_name" => ["foo"]];
        $_POST = ["coco_do" => "delete"];
        $cocoService = $this->cocoService([
            "./content/coco/20230306_120000_foo.htm",
            "./content/coco/foo.htm",
        ]);
        $sut = new MainAdminController($cocoService, $this->csrfProtector(true), $this->view());
        $response = $sut($this->request("delete"));
        $this->assertEquals("Coco – Co-Contents", $response->title());
        Approvals::verifyHtml($response->output());
    }

    private function request(string $action = ""): Request
    {
        $request = $this->createMock(Request::class);
        $request->method("sn")->willReturn("/");
        $request->method("action")->willReturn($action);
        $request->method("forms")->willReturn(new Forms);
        return $request;
    }

    private function cocoService(array $delete = []): CocoService
    {
        $cocoService = $this->createMock(CocoService::class);
        $cocoService->method("findAllNames")->willReturn(["foo", "bar"]);
        $cocoService->method("delete")->willReturn($delete);
        return $cocoService;
    }

    private function csrfProtector(bool $check = false): CsrfProtector
    {
        $csrfProtector = $this->createMock(CsrfProtector::class);
        $csrfProtector->method("token")->willReturn("eee5e668b3bcc9b71a9e4cc1aa76393f");
        $csrfProtector->expects($check ? $this->once() : $this->never())->method("check");
        return $csrfProtector;
    }

    private function view(): View
    {
        return new View("./views/", XH_includeVar("./languages/en.php", "plugin_tx")["coco"]);
    }
}
