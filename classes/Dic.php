<?php

/**
 * Copyright 2012-2021 Christoph M. Becker
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

use Coco\Infra\Backups;
use Coco\Infra\CocoService;
use Coco\Infra\CsrfProtector;
use Coco\Infra\IdGenerator;
use Coco\Infra\Pages;
use Coco\Infra\SystemChecker;
use Coco\Infra\XhStuff;
use Plib\HtmlView as View;

class Dic
{
    public static function makeBackupController(): BackupController
    {
        global $cf;
        return new BackupController(
            (int) $cf['backup']['numberoffiles'],
            self::makeCocoService(),
            new Backups,
            self::makeView()
        );
    }

    public static function makeMainController(): MainController
    {
        return new MainController(
            self::makeCocoService(),
            new CsrfProtector,
            new Pages,
            new XhStuff,
            self::makeView()
        );
    }

    public static function makeSearch(): Search
    {
        return new Search(
            self::makeCocoService(),
            new Pages,
            new XhStuff,
            self::makeView()
        );
    }

    public static function makeMainAdminController(): MainAdminController
    {
        return new MainAdminController(
            self::makeCocoService(),
            new CsrfProtector,
            self::makeView()
        );
    }

    public static function makePluginInfo(): PluginInfo
    {
        global $pth;
        return new PluginInfo(
            $pth["folder"]["plugins"] . "coco/",
            self::makeCocoService(),
            new SystemChecker,
            self::makeView()
        );
    }

    private static function makeCocoService(): CocoService
    {
        global $pth;

        return new CocoService(
            "{$pth['folder']['content']}coco",
            $pth['file']['content'],
            new Pages,
            new IdGenerator()
        );
    }

    private static function makeView(): View
    {
        global $pth, $plugin_tx;

        return new View($pth["folder"]["plugins"] . "coco/views", $plugin_tx["coco"]);
    }
}
