<?php

namespace Coco;

use PHPUnit\Framework\TestCase;

class DicTest extends TestCase
{
    public function setUp(): void
    {
        global $pth, $c, $cf, $plugin_tx;

        $pth = ["folder" => ["content" => "", "plugins" => ""], "file" => ["content" => ""]];
        $c = [];
        $cf = ["backup" => ["numberoffiles" => ""], "language" => ["default" => ""]];
        $plugin_tx = ["coco" => []];
    }

    public function testMakesMain(): void
    {
        $this->assertInstanceOf(Main::class, Dic::makeMain());
    }

    public function testMakesCoco(): void
    {
        $this->assertInstanceOf(Coco::class, Dic::makeCoco());
    }

    public function testMakesSearch(): void
    {
        $this->assertInstanceOf(Search::class, Dic::makeSearch());
    }

    public function testMakesCocoAdmin(): void
    {
        $this->assertInstanceOf(CocoAdmin::class, Dic::makeCocoAdmin());
    }

    public function testMakesPluginInfo(): void
    {
        $this->assertInstanceOf(PluginInfo::class, Dic::makePluginInfo());
    }
}
