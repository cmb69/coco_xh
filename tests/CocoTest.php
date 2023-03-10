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
use Coco\Infra\Pages;
use Coco\Infra\Repository;
use Coco\Infra\Request;
use Coco\Infra\View;
use Coco\Infra\XhStuff;
use PHPUnit\Framework\TestCase;

class CocoTest extends TestCase
{
    public function testRendersCoco(): void
    {
        $sut = new Coco($this->repository(), $this->csrfProtector(), $this->xhStuff(), $this->view());
        $response = $sut($this->request(), "foo", false, "100%");
        Approvals::verifyHtml($response->output());
    }

    public function testRendersCocoEditor(): void
    {
        $sut = new Coco($this->repository(), $this->csrfProtector(), $this->xhStuff(), $this->view());
        $response = $sut($this->request("edit"), "foo", false, "100%");
        Approvals::verifyHtml($response->output());
    }

    public function testRendersSaveButtonIfNoEditorIsConfigured(): void
    {
        $sut = new Coco($this->repository(), $this->csrfProtector(), $this->xhStuff(false), $this->view());
        $response = $sut($this->request("edit"), "foo", false, "100%");
        Approvals::verifyHtml($response->output());
    }

    public function testRedirectsAfterSavingContent(): void
    {
        $sut = new Coco($this->repository(true), $this->csrfProtector(true), $this->xhStuff(), $this->view());
        $request = $this->request("do_edit");
        $request->method("cocoText")->willReturn("some content");
        $response = $sut($request, "foo", false, "100%");
        $this->assertEquals("http://example.com/?", $response->location());
    }

    public function testReportsErrorOnFailureToSaveContent(): void
    {
        $_POST = ["coco_text_foo" => "some content"];
        $sut = new Coco($this->repository(false), $this->csrfProtector(true), $this->xhStuff(), $this->view());
        $request = $this->request("do_edit");
        $request->method("cocoText")->willReturn("some content");
        $response = $sut($request, "foo", false, "100%");
        Approvals::verifyHtml($response->output());
    }

    public function testReportsIllegalCocoName(): void
    {
        $sut = new Coco($this->repository(), $this->csrfProtector(), $this->xhStuff(), $this->view());
        $response = $sut($this->request(), "foo bar", false, "100%");
        Approvals::verifyHtml($response->output());
    }

    public function testIgnoresSearching(): void
    {
        $sut = new Coco($this->repository(), $this->csrfProtector(), $this->xhStuff(), $this->view());
        $response = $sut($this->request("", -1), "foo", false, "100%");
        $this->assertEquals("", $response->output());
    }

    private function request(string $action = "", int $page = 1): Request
    {
        $request = $this->createMock(Request::class);
        $request->method("cocoAction")->willReturn($action);
        $request->method("search")->willReturn("with");
        $request->method("s")->willReturn($page);
        return $request;
    }

    private function repository(?bool $save = null): Repository
    {
        $repository = $this->createMock(Repository::class);
        $repository->method("find")->willReturn("<p>some HTML with {{{trim('scripting')}}}</p>");
        $repository->method("filename")->willReturn("./content/coco/foo.htm");
        $repository->expects($save !== null ? $this->once() : $this->never())->method("save")->willReturn((bool) $save);
        return $repository;
    }

    private function csrfProtector(bool $check = false)
    {
        $csrfProtector = $this->createMock(CsrfProtector::class);
        $csrfProtector->method("token")->willReturn("eee5e668b3bcc9b71a9e4cc1aa76393f");
        $csrfProtector->expects($check ? $this->once() : $this->never())->method("check");
        return $csrfProtector;
    }

    private function pages(): Pages
    {
        $pages = $this->createMock(Pages::class);
        $pages->method("count")->willReturn(10);
        return $pages;
    }

    private function xhStuff($editor = "tinymce.init('coco_text_foo');"): XhStuff
    {
        $xhStuff = $this->createMock(XhStuff::class);
        $xhStuff->method("highlightSearchWords")->willReturn(
            "<p>some HTML <span class=\"highlight\">with</span> scripting</p>"
        );
        $xhStuff->method("replaceEditor")->willReturn($editor);
        return $xhStuff;
    }

    private function view(): View
    {
        return new View("./views/", XH_includeVar("./languages/en.php", "plugin_tx")["coco"]);
    }
}
