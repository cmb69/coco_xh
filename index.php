<?php

/**
 * Copyright 2012-2017 Christoph M. Becker
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

/*
 * Prevent direct access.
 */
if (!defined('CMSIMPLE_XH_VERSION')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

define('COCO_VERSION', '1.0');

/**
 * @return string
 */
function Coco_dataFolder()
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
 * @return array
 */
function Coco_cocos()
{
    $cocos = glob(Coco_dataFolder() . '*.htm');
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
 * @return string
 */
function Coco_get($name, $i)
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
        $fn = Coco_dataFolder() . $name . '.htm';
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
 */
function Coco_set($name, $i, $text)
{
    global $pth, $cl, $l, $h, $cf, $pd_router;

    $fn = Coco_dataFolder() . $name . '.htm';
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
function Coco_backup()
{
    global $cf, $tx, $backupDate;

    $dir = Coco_dataFolder();
    if (!isset($backupDate)) {
        $backupDate = date("Ymd_His");
    }
    $o = '';
    foreach (Coco_cocos() as $coco) {
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
function coco($name, $config = false, $height = '100%')
{
    global $adm, $edit, $s, $cl, $plugin_tx;

    if (!preg_match('/^[a-z_0-9]+$/su', $name)) {
        return XH_message('fail', $plugin_tx['coco']['error_invalid_name']);
    }
    if ($s < 0 || $s >= $cl) {
        return '';
    }
    $controller = new Coco\MainController($name, $config, $height);
    ob_start();
    if ($adm && $edit) {
        $controller->editAction();
    } else {
        $controller->defaultAction();
    }
    return ob_get_clean();
}

$pd_router->add_interest('coco_id');

if ($f == 'xh_loggedout') {
    $o .= Coco_backup();
}
