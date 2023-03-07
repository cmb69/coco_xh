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
use Coco\Infra\FakePages;
use Coco\Infra\FakeRequest;
use Coco\Infra\FakeXhStuff;
use Coco\Infra\View;
use PHPUnit\Framework\TestCase;

class MainControllerTest extends TestCase
{
    public function testRendersCoco(): void
    {
        $_GET = ["search" => "with"];
        $sut = $this->sut();
        $response = $sut(new FakeRequest(["s" => 1]), "foo", false, "100%");
        Approvals::verifyHtml($response);
    }

    public function testRendersCocoEditor(): void
    {
        $sut = $this->sut();
        $response = $sut(new FakeRequest(["adm" => true, "edit" => true, "s" => 1]), "foo", false, "100%");
        Approvals::verifyHtml($response);
    }

    public function testSaveIsCsrfProtected(): void
    {
        $_POST = ["coco_text_foo" => "some content"];
        $sut = $this->sut(["csrf" => ["check" => true]]);
        $this->expectExceptionMessage("CSRF check failed!");
        $sut(new FakeRequest(["adm" => true, "edit" => true, "s" => 1]), "foo", false, "100%");
    }

    public function testRendersCocoEditorAfterSavingContent(): void
    {
        $_POST = ["coco_text_foo" => "some content"];
        $sut = $this->sut();
        $response = $sut(new FakeRequest(["adm" => true, "edit" => true, "s" => 1]), "foo", false, "100%");
        Approvals::verifyHtml($response);
    }

    public function testReportsErrorOnFailureToSaveContent(): void
    {
        $_POST = ["coco_text_foo" => "some content"];
        $sut = $this->sut(["cocoService" => ["save" => false]]);
        $response = $sut(new FakeRequest(["adm" => true, "edit" => true, "s" => 1]), "foo", false, "100%");
        Approvals::verifyHtml($response);
    }

    public function testReportsIllegalCocoName(): void
    {
        $sut = $this->sut();
        $response = $sut(new FakeRequest(["s" => 1]), "foo bar", false, "100%");
        Approvals::verifyHtml($response);
    }

    public function testIgnoresSearching(): void
    {
        $sut = $this->sut();
        $response = $sut(new FakeRequest(["s" => -1]), "foo", false, "100%");
        $this->assertEquals("", $response);
    }

    private function sut(array $options = []): MainController
    {
        return new MainController(
            new FakeCocoService($options["cocoService"] ?? []),
            new FakeCsrfProtector($options["csrf"] ?? []),
            new FakePages(["count" => 10]),
            new FakeXhStuff,
            new View("./views/", XH_includeVar("./languages/en.php", "plugin_tx")["coco"])
        );
    }
}
