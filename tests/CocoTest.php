<?php

namespace Coco;

use ApprovalTests\Approvals;
use Coco\Infra\Repository;
use Coco\Infra\RepositoryException;
use Coco\Infra\XhStuff;
use PHPUnit\Framework\TestCase;
use Plib\CsrfProtector;
use Plib\FakeRequest;
use Plib\View;

class CocoTest extends TestCase
{
    public function testRendersCoco(): void
    {
        $sut = $this->sut();
        $request = new FakeRequest(["url" => "http://example.com/?&search=with"]);
        $response = $sut($request, "foo", false, "100%");
        $this->assertEquals("<p>some HTML <span class=\"highlight\">with</span> scripting</p>", $response->output());
    }

    public function testRendersCocoEditor(): void
    {
        $sut = $this->sut();
        $request = new FakeRequest(["admin" => true, "edit" => true]);
        $response = $sut($request, "foo", false, "100%");
        Approvals::verifyHtml($response->output());
    }

    public function testRendersSaveButtonIfNoEditorIsConfigured(): void
    {
        $sut = $this->sut(["xhStuff" => $this->xhStuff(false)]);
        $request = new FakeRequest(["admin" => true, "edit" => true]);
        $response = $sut($request, "foo", false, "100%");
        Approvals::verifyHtml($response->output());
    }

    public function testRedirectsAfterSavingContent(): void
    {
        $sut = $this->sut(["repository" => $this->repository(true), "csrfProtector" => $this->csrfProtector(true)]);
        $request = new FakeRequest([
            "admin" => true,
            "edit" => true,
            "post" => ["coco_text_foo" => "some content"]],
        );
        $response = $sut($request, "foo", false, "100%");
        $this->assertEquals("http://example.com/", $response->location());
    }

    public function testReportsErrorOnFailureToSaveContent(): void
    {
        $sut = $this->sut(["repository" => $this->repository(false), "csrfProtector" => $this->csrfProtector(true)]);
        $request = new FakeRequest([
            "admin" => true,
            "edit" => true,
            "post" => ["coco_text_foo" => "some content"]],
        );
        $response = $sut($request, "foo", false, "100%");
        $this->assertStringContainsString("./content/coco/foo.htm could not be saved!", $response->output());
    }

    public function testReportsIllegalCocoName(): void
    {
        $sut = $this->sut();
        $request = new FakeRequest(["query" => "search=with"]);
        $response = $sut($request, "foo bar", false, "100%");
        $this->assertStringContainsString("Co-content names may contain a-z, 0-9 and _ only!", $response->output());
    }

    public function testIgnoresSearching(): void
    {
        $sut = $this->sut();
        $request = new FakeRequest(["query" => "search=with", "s" => -1]);
        $response = $sut($request, "foo", false, "100%");
        $this->assertEquals("", $response->output());
    }

    private function sut(array $deps = []): Coco
    {
        return new Coco(
            $deps["repository"] ?? $this->repository(),
            $deps["csrfProtector"] ?? $this->csrfProtector(),
            $deps["xhStuff"] ?? $this->xhStuff(),
            $this->view()
        );
    }

    private function repository(?bool $save = null): Repository
    {
        $repository = $this->createMock(Repository::class);
        $repository->method("find")->willReturn("<p>some HTML with {{{trim('scripting')}}}</p>");
        $repository->method("filename")->willReturn("./content/coco/foo.htm");
        $method = $repository->expects($save !== null ? $this->once() : $this->never())->method("save");
        if ($save === false) {
            $method->willThrowException(new RepositoryException());
        }
        return $repository;
    }

    private function csrfProtector(bool $check = false)
    {
        $csrfProtector = $this->createMock(CsrfProtector::class);
        $csrfProtector->method("token")->willReturn("eee5e668b3bcc9b71a9e4cc1aa76393f");
        $csrfProtector->expects($check ? $this->once() : $this->never())->method("check");
        return $csrfProtector;
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
