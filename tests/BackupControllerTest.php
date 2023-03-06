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
use PHPUnit\Framework\TestCase;
use Plib\HtmlView as View;

class BackupControllerTest extends TestCase
{
    public function testReportsBackupSuccess(): void
    {
        $sut = $this->sut();
        $response = $sut(strtotime("2023-03-06T12:00:00"));
        Approvals::verifyHtml($response);
    }

    public function testReportsFailureToCreateBackup(): void
    {
        $sut = $this->sut(["backups" => ["create" => false]]);
        $response = $sut(strtotime("2023-03-06T12:00:00"));
        Approvals::verifyHtml($response);
    }

    public function testReportsFailureToDeleteSurplusBackups(): void
    {
        $sut = $this->sut(["backups" => ["delete" => false]]);
        $response = $sut(strtotime("2023-03-06T12:00:00"));
        Approvals::verifyHtml($response);
    }

    private function sut($options = []): BackupController
    {
        return new BackupController(
            2,
            new FakeCocoService,
            new FakeBackups($options["backups"] ?? []),
            new View("./views/", XH_includeVar("./languages/en.php", "plugin_tx")["coco"])
        );
    }
}
