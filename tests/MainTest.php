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
use Coco\Infra\Request;
use Coco\Infra\View;
use PHPUnit\Framework\TestCase;

class MainTest extends TestCase
{
    public function testDoesNothingWhenNotLoggingOut(): void
    {
        $sut = new Main($this->conf(), $this->repository(), $this->view());
        $response = $sut($this->request(false));
        $this->assertEquals("", $response->output());
    }

    public function testReportsBackupSuccess(): void
    {
        $sut = new Main($this->conf(), $this->repository(), $this->view());
        $response = $sut($this->request());
        Approvals::verifyHtml($response->output());
    }

    public function testReportsFailureToCreateBackup(): void
    {
        $sut = new Main($this->conf(), $this->repository(false), $this->view());
        $response = $sut($this->request());
        Approvals::verifyHtml($response->output());
    }

    public function testReportsFailureToDeleteSurplusBackups(): void
    {
        $sut = new Main($this->conf(), $this->repository(true, false), $this->view());
        $response = $sut($this->request());
        Approvals::verifyHtml($response->output());
    }

    private function request(bool $logout = true): Request
    {
        $request = $this->createStub(Request::class);
        $request->method("requestTime")->willReturn(strtotime("2023-03-06T12:00:00"));
        $request->method("logOut")->willReturn($logout);
        return $request;
    }

    private function repository(bool $create = true, bool $delete = true): Repository
    {
        $repository = $this->createStub(Repository::class);
        $repository->method("dataFolder")->willReturn("./content/coco/");
        $repository->method("findAllNames")->willReturn(["foo", "bar"]);
        $repository->method("filename")->willReturnCallback(function ($coconame, $date = null) {
            return "./content/coco/" . ($date !== null ? "{$date}_" : "") . "$coconame.htm";
        });
        $repository->method("backup")->willReturn($create);
        $repository->method("delete")->willReturn($delete);
        $repository->method("findAllBackups")->willReturnOnConsecutiveCalls([
            ["foo", "20230304_120000"],
            ["foo", "20230305_120000"],
            ["foo", "20230306_120000"],
        ], [
            ["bar", "20230304_120000"],
            ["bar", "20230305_120000"],
            ["bar", "20230306_120000"],
        ]);
        return $repository;
    }

    private function view(): View
    {
        return new View("./views/", XH_includeVar("./languages/en.php", "plugin_tx")["coco"]);
    }

    private function conf(): array
    {
        return ["backup_numberoffiles" => 2];
    }
}
