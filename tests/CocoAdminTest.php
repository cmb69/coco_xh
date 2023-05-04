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
use Coco\Infra\RequestStub;
use Coco\Infra\View;
use PHPUnit\Framework\TestCase;

class CocoAdminTest extends TestCase
{
    public function testRendersCocoOverview(): void
    {
        $sut = $this->sut();
        $response = $sut(new RequestStub());
        $this->assertEquals("Coco – Co-Contents", $response->title());
        Approvals::verifyHtml($response->output());
    }

    public function testRendersDeleteConfirmation(): void
    {
        $sut = $this->sut();
        $request = new RequestStub(["query" => "action=delete&coco_name[]=foo"]);
        $response = $sut($request);
        $this->assertEquals("Coco – Co-Contents", $response->title());
        Approvals::verifyHtml($response->output());
    }

    public function testSuccessfulDeletionRedirects(): void
    {
        $sut = $this->sut(["csrfProtector" => $this->csrfProtector(true)]);
        $request = new RequestStub([
            "query" => "action=delete&coco_name[]=foo",
            "post" => ["coco_do" => "delete"],
        ]);
        $response = $sut($request);
        $this->assertEquals("http://example.com/?coco&admin=plugin_main", $response->location());
    }

    public function testFailureToDeleteIsReported(): void
    {
        $sut = $this->sut(["repository" => $this->repository(false), "csrfProtector" => $this->csrfProtector(true)]);
        $request = new RequestStub([
            "query" => "action=delete&coco_name[]=foo",
            "post" => ["coco_do" => "delete"],
        ]);
        $response = $sut($request);
        $this->assertEquals("Coco – Co-Contents", $response->title());
        $this->assertStringContainsString("./content/coco/foo.htm could not be deleted!", $response->output());
    }

    private function sut(array $deps = []): CocoAdmin
    {
        return new CocoAdmin(
            $deps["repository"] ?? $this->repository(),
            $deps["csrfProtector"] ?? $this->csrfProtector(),
            $this->view()
        );
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
