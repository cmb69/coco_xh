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
            $o .= Plugin::backup();
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
                (new InfoController(self::view()))->defaultAction();
                $o .= ob_get_clean();
                break;
            case 'plugin_main':
                $controller = new MainAdminController($_XH_csrfProtection, self::view());
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
    public static function dataFolder()
    {
        global $pth;

        $fn = $pth['folder']['content'] . 'coco/';
        if (file_exists($fn)) {
            if (!is_dir($fn)) {
                e('cntopen', 'folder', $fn);
            }
        } else {
            if (mkdir($fn, 0777, true)) {
                chmod($fn, 0777);
            } else {
                e('cntwriteto', 'folder', $fn);
            }
        }
        return $fn;
    }

    /**
     * @return string[]
     */
    public static function cocos()
    {
        $cocos = glob(self::dataFolder() . '*.htm');
        $func = function ($fn) {
            return basename($fn, '.htm');
        };
        $cocos = array_map($func, $cocos);
        $func = function ($fn) {
            return !preg_match('/^\d{8}_\d{6}_/', $fn);
        };
        $cocos = array_filter($cocos, $func);
        return $cocos;
    }

    /**
     * @param string $name
     * @param int $i
     * @return string|false
     */
    public static function get($name, $i)
    {
        global $cf, $pd_router;
        static $curname = null;
        static $text = null;

        $pd = $pd_router->find_page($i);
        if (empty($pd['coco_id'])) {
            return '';
        }
        if ($name != $curname) {
            $curname = $name;
            $fn = self::dataFolder() . $name . '.htm';
            if (!is_readable($fn) || ($text = XH_readFile($fn)) === false) {
                e('cntopen', 'file', $fn);
                return false;
            }
        }
        $ml = $cf['menu']['levels'];
        preg_match(
            '/<h[1-' . $ml . '].*?id="' . $pd['coco_id'] . '".*?>.*?'
            . '<\/h[1-' . $ml . ']>(.*?)<(?:h[1-' . $ml . ']|\/body)/isu',
            $text,
            $matches
        );
        return !empty($matches[1]) ? trim($matches[1]) : '';
    }

    /**
     * @param string $name
     * @param int $i
     * @param string $text
     * @return void
     */
    public static function set($name, $i, $text)
    {
        global $pth, $cl, $l, $h, $cf, $pd_router;

        $fn = self::dataFolder() . $name . '.htm';
        $old = is_readable($fn) ? XH_readFile($fn) : '';
        $ml = $cf['menu']['levels'];
        $cnt = '<html>' . PHP_EOL . '<body>' . PHP_EOL;
        for ($j = 0; $j < $cl; $j++) {
            $pd = $pd_router->find_page($j);
            if (empty($pd['coco_id'])) {
                $pd['coco_id'] = uniqid();
                $pd_router->update($j, $pd);
            }
            $cnt .= '<h' . $l[$j] . ' id="' . $pd['coco_id'] . '">' . $h[$j]
                . '</h' . $l[$j] . '>' . PHP_EOL;
            if ($j == $i) {
                $text = preg_replace('/<h' . $ml . '.*?>.*?<\/h' . $ml . '>/isu', '', $text);
                $text = trim($text);
                $text = preg_replace('/(<\/?h)[1-' . $ml . ']/is', '${1}' . ($ml + 1), $text);
                if (!empty($text)) {
                    $cnt .= $text . PHP_EOL;
                }
            } else {
                preg_match(
                    '/<h[1-' . $ml . '].*?id="' . $pd['coco_id'] . '".*?>.*?'
                    . '<\/h[1-' . $ml . ']>(.*?)<(?:h[1-' . $ml . ']|\/body)/isu',
                    $old,
                    $matches
                );
                $cnt .= isset($matches[1]) && ($match = trim($matches[1])) != ''
                    ? $match . PHP_EOL
                    : '';
            }
        }
        $cnt .= '</body>' . PHP_EOL . '</html>' . PHP_EOL;
        if (XH_writeFile($fn, $cnt) !== false) {
            touch($pth['file']['content']);
        } else {
            e('cntwriteto', 'file', $fn);
        }
    }

    /**
     * @return string
     */
    public static function backup()
    {
        global $cf, $tx, $backupDate;

        $dir = self::dataFolder();
        if (!isset($backupDate)) {
            $backupDate = date("Ymd_His");
        }
        $o = '';
        foreach (self::cocos() as $coco) {
            $fn = $dir . $backupDate . '_' . $coco . '.htm';
            if (copy($dir . $coco . '.htm', $fn)) {
                $o .= XH_message(
                    'info',
                    ucfirst($tx['filetype']['backup']) . ' ' . $fn . ' '
                    . $tx['result']['created']
                ) . PHP_EOL;
                $bus = glob($dir . '????????_??????_' . $coco . '.htm');
                for ($i = 0; $i < count($bus) - $cf['backup']['numberoffiles']; $i++) {
                    if (unlink($bus[$i])) {
                        $o .= XH_message(
                            'info',
                            ucfirst($tx['filetype']['backup']) . ' ' . $bus[$i]
                            . ' ' . $tx['result']['deleted']
                        ) . PHP_EOL;
                    } else {
                        e('cntdelete', 'backup', $bus[$i]);
                    }
                }
            } else {
                e('cntsave', 'backup', $fn);
            }
        }
        return $o;
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
        $controller = new MainController($name, $config, $height, $_XH_csrfProtection, self::view());
        ob_start();
        if ($adm && $edit) {
            $controller->editAction();
        } else {
            $controller->defaultAction();
        }
        return ob_get_clean();
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
        $words = preg_split('/\s+/isu', $search, null, PREG_SPLIT_NO_EMPTY);
        $ta = self::searchContent(null, $words);
        foreach (self::cocos() as $name) {
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
     * @global int   The number of pages.
     * @global array The configuration of the core.
     */
    private static function searchContent($name, array $words)
    {
        global $c, $cl, $cf;

        $ta = array();
        for ($i = 0; $i < $cl; $i++) {
            if (!hide($i) || $cf['hidden']['pages_search'] == 'true') {
                $text = !isset($name) ? $c[$i] : self::get($name, $i);
                if (self::doSearch($words, $text)) {
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
     * @return View
     */
    private static function view()
    {
        global $pth, $plugin_tx;

        return new View($plugin_tx["coco"], "{$pth['folder']['plugins']}coco/views");
    }
}
