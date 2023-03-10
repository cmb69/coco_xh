<?php

/**
 * Copyright 2012-2023 Christoph M. Becker
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

use Coco\Infra\CsrfProtector;
use Coco\Infra\IdGenerator;
use Coco\Infra\Pages;
use Coco\Infra\Repository;
use Coco\Infra\SystemChecker;
use Coco\Infra\View;
use Coco\Infra\XhStuff;

class Dic
{
    public static function makeMain(): Main
    {
        return new Main(
            self::makeConf(),
            self::makeRepository(),
            self::makeView()
        );
    }

    public static function makeCoco(): Coco
    {
        return new Coco(
            self::makeRepository(),
            new CsrfProtector,
            new XhStuff,
            self::makeView()
        );
    }

    public static function makeSearch(): Search
    {
        return new Search(
            self::makeRepository(),
            new Pages,
            new XhStuff,
            self::makeView()
        );
    }

    public static function makeCocoAdmin(): CocoAdmin
    {
        return new CocoAdmin(
            self::makeRepository(),
            new CsrfProtector,
            self::makeView()
        );
    }

    public static function makePluginInfo(): PluginInfo
    {
        global $pth;
        return new PluginInfo(
            $pth["folder"]["plugins"] . "coco/",
            self::makeRepository(),
            new SystemChecker,
            self::makeView()
        );
    }

    private static function makeRepository(): Repository
    {
        global $pth;

        return new Repository(
            $pth['folder']['content'] . "coco/",
            $pth['file']['content'],
            new Pages,
            new IdGenerator()
        );
    }

    private static function makeView(): View
    {
        global $pth, $plugin_tx;

        return new View($pth["folder"]["plugins"] . "coco/views/", $plugin_tx["coco"]);
    }

    /** @return array<string,string> */
    private static function makeConf(): array
    {
        global $cf;
        return ["backup_numberoffiles" => $cf['backup']['numberoffiles']];
    }
}
