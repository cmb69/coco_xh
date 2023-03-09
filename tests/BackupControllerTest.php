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

namespace Coco\Infra;

use ApprovalTests\Approvals;
use Coco\BackupController;
use Coco\Infra\View;
use PHPUnit\Framework\TestCase;

class BackupControllerTest extends TestCase
{
    public function testReportsBackupSuccess(): void
    {
        $sut = new BackupController(2, $this->cocoService(), $this->backups(), $this->view());
        $response = $sut($this->request());
        Approvals::verifyHtml($response);
    }

    public function testReportsFailureToCreateBackup(): void
    {
        $sut = new BackupController(2, $this->cocoService(), $this->backups(false), $this->view());
        $response = $sut($this->request());
        Approvals::verifyHtml($response);
    }

    public function testReportsFailureToDeleteSurplusBackups(): void
    {
        $sut = new BackupController(2, $this->cocoService(), $this->backups(true, false), $this->view());
        $response = $sut($this->request());
        Approvals::verifyHtml($response);
    }

    private function request(): Request
    {
        $request = $this->createStub(Request::class);
        $request->method("server")->with("REQUEST_TIME")->willReturn((string) strtotime("2023-03-06T12:00:00"));
        return $request;
    }

    private function cocoService(): CocoService
    {
        $cocoService = $this->createStub(CocoService::class);
        $cocoService->method("dataDir")->willReturn("./content/coco");
        $cocoService->method("findAllNames")->willReturn(["foo", "bar"]);
        return $cocoService;
    }

    private function backups(bool $create = true, bool $delete = true): Backups
    {
        $backups = $this->createStub(Backups::class);
        $backups->method("all")->willReturnOnConsecutiveCalls([
            "./content/coco/20230304_120000_foo.htm",
            "./content/coco/20230305_120000_foo.htm",
            "./content/coco/20230306_120000_foo.htm",
        ], [
            "./content/coco/20230304_120000_bar.htm",
            "./content/coco/20230305_120000_bar.htm",
            "./content/coco/20230306_120000_bar.htm",
        ]);
        $backups->method("create")->willReturn($create);
        $backups->method("delete")->willReturn($delete);
        $backups->method("filename")->willReturnOnConsecutiveCalls(
            "./content/coco/20230306_120000_foo.htm",
            "./content/coco/20230306_120000_bar.htm",
        );
        return $backups;
    }

    private function view(): View
    {
        return new View("./views/", XH_includeVar("./languages/en.php", "plugin_tx")["coco"]);
    }
}
