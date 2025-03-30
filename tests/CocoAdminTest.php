<?php

namespace Coco;

use ApprovalTests\Approvals;
use Coco\Infra\Repository;
use Coco\Infra\RepositoryException;
use PHPUnit\Framework\TestCase;
use Plib\CsrfProtector;
use Plib\FakeRequest;
use Plib\View;

class CocoAdminTest extends TestCase
{
    public function testRendersCocoOverview(): void
    {
        $sut = $this->sut();
        $response = $sut(new FakeRequest());
        $this->assertEquals("Coco – Co-Contents", $response->title());
        Approvals::verifyHtml($response->output());
    }

    public function testRendersDeleteConfirmation(): void
    {
        $sut = $this->sut();
        $request = new FakeRequest(["url" => "http://example.com/?&action=delete&coco_name[]=foo"]);
        $response = $sut($request);
        $this->assertEquals("Coco – Co-Contents", $response->title());
        Approvals::verifyHtml($response->output());
    }

    public function testSuccessfulDeletionRedirects(): void
    {
        $sut = $this->sut(["csrfProtector" => $this->csrfProtector(true)]);
        $request = new FakeRequest([
            "url" => "http://example.com/?&action=delete&coco_name[]=foo",
            "post" => ["coco_do" => "delete"],
        ]);
        $response = $sut($request);
        $this->assertEquals("http://example.com/?coco&admin=plugin_main", $response->location());
    }

    public function testFailureToDeleteIsReported(): void
    {
        $sut = $this->sut(["repository" => $this->repository(false), "csrfProtector" => $this->csrfProtector(true)]);
        $request = new FakeRequest([
            "url" => "http://example.com/?&action=delete&coco_name[]=foo",
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
        if (!$deleted) {
            $repository->method("delete")->willThrowException(new RepositoryException());
        }
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
