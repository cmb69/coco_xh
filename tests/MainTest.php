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
use Coco\Infra\RepositoryException;
use PHPUnit\Framework\TestCase;
use Plib\FakeRequest;
use Plib\View;

class MainTest extends TestCase
{
    // public function testDoesNothingWhenNotLoggingOut(): void
    // {
    //     $sut = $this->sut();
    //     $response = $sut(new FakeRequest());
    //     $this->assertEquals("", $response->output());
    // }

    public function testReportsBackupSuccess(): void
    {
        $sut = $this->sut();
        $request = new FakeRequest(["time" => strtotime("2023-03-06T12:00:00")]);
        $response = $sut($request);
        Approvals::verifyHtml($response->output());
    }

    public function testReportsFailureToCreateBackup(): void
    {
        $sut = $this->sut(["repository" => $this->repository(false)]);
        $request = new FakeRequest(["time" => strtotime("2023-03-06T12:00:00")]);
        $response = $sut($request);
        Approvals::verifyHtml($response->output());
    }

    public function testReportsFailureToDeleteSurplusBackups(): void
    {
        $sut = $this->sut(["repository" => $this->repository(true, false)]);
        $request = new FakeRequest(["time" => strtotime("2023-03-06T12:00:00")]);
        $response = $sut($request);
        Approvals::verifyHtml($response->output());
    }

    private function sut(array $deps = []): Main
    {
        return new Main(
            $this->conf(),
            $deps["repository"] ?? $this->repository(),
            $this->view()
        );
    }

    private function repository(bool $create = true, bool $delete = true): Repository
    {
        $repository = $this->createStub(Repository::class);
        $repository->method("dataFolder")->willReturn("./content/coco/");
        $repository->method("findAllNames")->willReturn(["foo", "bar"]);
        $repository->method("filename")->willReturnCallback(function ($coconame, $date = null) {
            return "./content/coco/" . ($date !== null ? "{$date}_" : "") . "$coconame.htm";
        });
        if (!$create) {
            $repository->method("backup")->willThrowException(new RepositoryException());
        }
        if (!$delete) {
            $repository->method("delete")->willThrowException(new RepositoryException());
        }
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
