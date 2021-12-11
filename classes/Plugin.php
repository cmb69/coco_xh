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

use Plib\HtmlView as View;
use XH\Pages;

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
        global $o, $admin, $action, $_XH_csrfProtection;

        $o .= print_plugin_admin('on');
        switch ($admin) {
            case '':
                ob_start();
                (new InfoController(new SystemCheckService(self::cocoService()), self::view()))->defaultAction();
                $o .= ob_get_clean();
                break;
            case 'plugin_main':
                $controller = new MainAdminController(
                    self::url(),
                    self::cocoService(),
                    $_XH_csrfProtection,
                    self::view()
                );
                ob_start();
                switch ($action) {
                    case 'delete':
                        $controller->deleteAction();
                        break;
                    default:
                        $controller->defaultAction();
                }
                $o .= ob_get_clean();
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

        ob_start();
        $controller = new BackupController((int) $cf['backup']['numberoffiles'], self::cocoService(), self::view());
        $controller->execute(date("Ymd_His"));
        return (string) ob_get_clean();
    }

    /**
     * @param string $name
     * @param string $config
     * @param string $height
     * @return string
     */
    public static function coco($name, $config, $height)
    {
        global $adm, $edit, $s, $cl, $plugin_tx, $_XH_csrfProtection;

        if (!preg_match('/^[a-z_0-9]+$/su', $name)) {
            return XH_message('fail', $plugin_tx['coco']['error_invalid_name']);
        }
        if ($s < 0 || $s >= $cl) {
            return '';
        }
        $controller = new MainController(
            $name,
            $config,
            $height,
            self::cocoService(),
            $_XH_csrfProtection,
            self::view()
        );
        ob_start();
        if ($adm && $edit) {
            $controller->editAction();
        } else {
            $controller->defaultAction();
        }
        return (string) ob_get_clean();
    }

    /**
     * Searches the contents
     *
     * @return void
     *
     * @global string The search string.
     * @global string The script name.
     * @global array  The headings of the pages.
     * @global array  The URLs of the pages.
     * @global array  The configuration of the plugins.
     * @global array  The localization of the plugins.
     */
    public static function search()
    {
        global $title, $o, $search, $h, $tx, $plugin_tx;

        $ptx = $plugin_tx['coco'];
        $title = $tx['title']['search'];
        $words = preg_split('/\s+/isu', $search, 0, PREG_SPLIT_NO_EMPTY) ?: [];
        $ta = self::searchContent(null, $words);
        foreach (self::cocoService()->findAllNames() as $name) {
            $ta = array_merge($ta, self::searchContent($name, $words));
        }
        $ta = array_unique($ta);
        sort($ta);
        $o .= '<h1>' . $ptx['search_result'] . '</h1>' . PHP_EOL
            . '<p>"' . htmlspecialchars($search, ENT_COMPAT, 'UTF-8') . '" ';
        if (count($ta) == 0) {
            $o .= $ptx['search_notfound'];
        } else {
            $o .= $ptx['search_foundin'] . ' ' . count($ta) . ' ';
            if (count($ta) > 1) {
                $o .= $ptx['search_pgplural'];
            } else {
                $o .= $ptx['search_pgsingular'];
            }
            $o .= ':';
        }
        $o .= '</p>' . PHP_EOL;
        if (count($ta) > 0) {
            $o .= '<ul>' . PHP_EOL;
            $words = implode(',', $words);
            foreach ($ta as $i) {
                $o .= '<li>' . a($i, '&amp;search=' . urlencode($words))
                    . $h[$i] . '</a></li>' . PHP_EOL;
            }
            $o .= '</ul>' . PHP_EOL;
        }
    }

    /**
     * Returns a list of all pages that contain all words in a co-content.
     * If $name === NULL the main content will be searched.
     *
     * @param string|null $name  A co-content name.
     * @param string[]  $words An array of words.
     *
     * @return int[]
     *
     * @global array The content of the pages.
     * @global array The configuration of the core.
     */
    private static function searchContent($name, array $words)
    {
        global $c, $cf;

        if ($name === null) {
            $cocos = $c;
        } else {
            $cocos = self::cocoService()->findAll($name);
        }
        $ta = array();
        foreach ($cocos as $i => $coco) {
            if (!hide($i) || $cf['show_hidden']['pages_search'] == 'true') {
                if (self::doSearch($words, $coco)) {
                    $ta[] = $i;
                }
            }
        }
        return $ta;
    }

    /**
     * Returns whether all words are contained in a text.
     *
     * @param string[] $words An array of words.
     * @param string $text  A text to search in.
     *
     * @return bool
     */
    private static function doSearch(array $words, $text)
    {
        $text = strip_tags(evaluate_scripting($text));
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        $text = utf8_strtolower($text);
        foreach ($words as $word) {
            if (strpos($text, utf8_strtolower($word)) === false) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return CocoService
     */
    private static function cocoService()
    {
        global $pth, $pd_router;

        return new CocoService(
            "{$pth['folder']['content']}coco",
            $pth['file']['content'],
            new Pages(),
            $pd_router,
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
