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
            $o .= Dic::makeBackupController()(time());
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
        global $o, $admin, $action;

        $o .= print_plugin_admin('on');
        switch ($admin) {
            case '':
                $o .= Dic::makePluginInfo()();
                break;
            case 'plugin_main':
                $o .= Dic::makeMainAdminController()($action);
                break;
            default:
                $o .= plugin_admin_common();
        }
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

        return Dic::makeMainController()($adm && $edit, $cl, $s, $name, $config, $height);
    }

    /** @return void */
    public static function search()
    {
        global $o, $sn, $search;

        $o .= Dic::makeSearch()($sn, $search);
    }
}
