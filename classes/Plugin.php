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
use Plib\Url;

final class Plugin
{
    const VERSION = "2.0-dev";


    /**
     * @return void
     */
    public static function run()
    {
        global $pd_router, $f, $o;

        $pd_router->add_interest('coco_id');

        if ($f == 'xh_loggedout') {
            $o .= self::backup();
        }

        if (XH_ADM) { // @phpstan-ignore-line
            XH_registerStandardPluginMenuItems(true);
            if (XH_wantsPluginAdministration('coco')) {
                self::handlePluginAdministration();
            }
        }
    }

    /**
     * @return void
     */
    private static function handlePluginAdministration()
    {
        global $pth, $o, $admin, $action, $_XH_csrfProtection;

        $o .= print_plugin_admin('on');
        switch ($admin) {
            case '':
                $controller = new PluginInfo(
                    $pth["folder"]["plugin"],
                    self::cocoService(),
                    new SystemChecker,
                    self::view()
                );
                $o .= $controller();
                break;
            case 'plugin_main':
                $controller = new MainAdminController(
                    self::url(),
                    self::cocoService(),
                    new CsrfProtector,
                    self::view()
                );
                $o .= $controller($action);
                break;
            default:
                $o .= plugin_admin_common();
        }
    }

    /**
     * @return string
     */
    private static function backup()
    {
        global $cf;

        $controller = new BackupController(
            (int) $cf['backup']['numberoffiles'],
            self::cocoService(),
            new Backups,
            self::view()
        );
        return $controller(time());
    }

    /**
     * @param string $name
     * @param string $config
     * @param string $height
     * @return string
     */
    public static function coco($name, $config, $height)
    {
        global $adm, $edit, $s, $cl;

        $controller = new MainController(
            self::cocoService(),
            new CsrfProtector,
            new XhStuff,
            self::view()
        );
        return $controller($adm && $edit, $cl, $s, $name, $config, $height);
    }

    /** @return void */
    public static function search()
    {
        global $o, $sn, $search;

        $handler = new Search(
            self::cocoService(),
            new XhStuff,
            self::view()
        );
        $o .= $handler($sn, $search);
    }

    /**
     * @return CocoService
     */
    private static function cocoService()
    {
        global $pth;

        return new CocoService(
            "{$pth['folder']['content']}coco",
            $pth['file']['content'],
            new Pages,
            new IdGenerator()
        );
    }

    /**
     * @return View
     */
    private static function view()
    {
        global $pth, $plugin_tx;

        return new View("{$pth['folder']['plugins']}coco/views", $plugin_tx["coco"]);
    }

    private static function url(): Url
    {
        global $sl, $cf, $su;

        $base = preg_replace(['/index\.php$/', "/(?<=\\/)$sl\\/$/"], "", CMSIMPLE_URL);
        assert($base !== null);
        return new Url($base, $sl === $cf["language"]["default"] ? "" : $sl, $su);
    }
}
