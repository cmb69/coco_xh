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
use Coco\Infra\CsrfProtector;
use Coco\Infra\Repository;
use Coco\Infra\Request;
use Coco\Infra\View;
use PHPUnit\Framework\TestCase;

class CocoAdminTest extends TestCase
{
    public function testRendersCocoOverview(): void
    {
        $sut = new CocoAdmin($this->repository(), $this->csrfProtector(), $this->view());
        $response = $sut($this->request());
        $this->assertEquals("Coco – Co-Contents", $response->title());
        Approvals::verifyHtml($response->output());
    }

    public function testRendersDeleteConfirmation(): void
    {
        $sut = new CocoAdmin($this->repository(), $this->csrfProtector(), $this->view());
        $request = $this->request();
        $request->method("cocoAdminAction")->willReturn("delete");
        $request->method("cocoNames")->willReturn(["foo"]);
        $response = $sut($request);
        $this->assertEquals("Coco – Co-Contents", $response->title());
        Approvals::verifyHtml($response->output());
    }

    public function testSuccessfulDeletionRedirects(): void
    {
        $sut = new CocoAdmin($this->repository(), $this->csrfProtector(true), $this->view());
        $request = $this->request();
        $request->method("cocoAdminAction")->willReturn("do_delete");
        $request->method("cocoNames")->willReturn(["foo"]);
        $response = $sut($request);
        $this->assertEquals("http://example.com/?coco&admin=plugin_main", $response->location());
    }

    public function testFailureToDeleteIsReported(): void
    {
        $repository = $this->repository(false);
        $sut = new CocoAdmin($repository, $this->csrfProtector(true), $this->view());
        $request = $this->request();
        $request->method("cocoAdminAction")->willReturn("do_delete");
        $request->method("cocoNames")->willReturn(["foo"]);
        $response = $sut($request);
        $this->assertEquals("Coco – Co-Contents", $response->title());
        Approvals::verifyHtml($response->output());
    }

    private function request(): Request
    {
        $request = $this->createMock(Request::class);
        $request->method("sn")->willReturn("/");
        return $request;
    }

    private function repository(bool $deleted = true): Repository
    {
        $repository = $this->createMock(Repository::class);
        $repository->method("findAllNames")->willReturn(["foo", "bar"]);
        $repository->method("findAllBackups")->willReturn([["foo", "20230306_120000"]]);
        $repository->method("delete")->willReturn($deleted);
        $repository->method("filename")->willReturnOnConsecutiveCalls(
            "./content/coco/20230306_120000_foo.htm",
            "./content/coco/foo.htm"
        );
        return $repository;
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
