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

use PHPUnit\Framework\TestCase;

class DicTest extends TestCase
{
    public function setUp(): void
    {
        global $pth, $c, $cf, $plugin_tx, $sl, $su;

        $pth = ["folder" => ["content" => "", "plugins" => ""], "file" => ["content" => ""]];
        $c = [];
        $cf = ["backup" => ["numberoffiles" => ""], "language" => ["default" => ""]];
        $plugin_tx = ["coco" => []];
        $sl = "";
        $su = "";
    }

    public function testMakesBackupController(): void
    {
        $this->assertInstanceOf(BackupController::class, Dic::makeBackupController());
    }

    public function testMakesMainController(): void
    {
        $this->assertInstanceOf(MainController::class, Dic::makeMainController());
    }

    public function testMakesSearch(): void
    {
        $this->assertInstanceOf(Search::class, Dic::makeSearch());
    }

    public function testMakesMainAdminController(): void
    {
        $this->assertInstanceOf(MainAdminController::class, Dic::makeMainAdminController());
    }

    public function testMakesPluginInfo(): void
    {
        $this->assertInstanceOf(PluginInfo::class, Dic::makePluginInfo());
    }
}
