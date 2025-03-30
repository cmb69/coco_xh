<?php

namespace Coco;

use ApprovalTests\Approvals;
use Coco\Infra\Repository;
use PHPUnit\Framework\TestCase;
use Plib\FakeSystemChecker;
use Plib\View;

class PluginInfoTest extends TestCase
{
    public function testRendersPluginInfo(): void
    {
        $sut = $this->sut();
        $response = $sut();
        $this->assertEquals("Coco 2.0-dev", $response->title());
        Approvals::verifyHtml($response->output());
    }

    private function sut(): PluginInfo
    {
        $repository = $this->createStub(Repository::class);
        $repository->method("dataFolder")->willReturn("./content/coco/");
        $text = XH_includeVar("./languages/en.php", "plugin_tx")["coco"];
        return new PluginInfo(
            "./plugins/coco/",
            $repository,
            new FakeSystemChecker(),
            new View("./views/", $text)
        );
    }
}
