<?php

/**
 * Administration of Coco_XH.
 *
 * PHP versions 4 and 5
 *
 * @category  CMSimple_XH
 * @package   Coco
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2012-2014 Christoph M. Becker <http://3-magi.net>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://3-magi.net/?CMSimple_XH/Coco_XH
 */

/*
 * Prevent direct access
 */
if (!defined('CMSIMPLE_XH_VERSION')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

/**
 * Returns the plugin's version information view.
 *
 * @return string (X)HTML.
 *
 * @global array The paths of system files and folders.
 * @global array The localization of the plugins.
 */
function Coco_version()
{
    global $pth, $plugin_tx;

    $imagePath = $pth['folder']['plugins'] . 'coco/coco.png';
    return '<h1>Coco &ndash; ' . $plugin_tx['coco']['label_info'] . '</h1>'
        . PHP_EOL
        . tag(
            'img class="coco_plugin_icon" src="' . $imagePath . '" alt="'
            . $plugin_tx['coco']['alt_logo'] . '"'
        )
        . '<p>Version: ' . COCO_VERSION . '</p>' . PHP_EOL
        . '<p>Copyright &copy; 2012-2014 <a href="http://3-magi.net">'
        . 'Christoph M. Becker</a></p>' . PHP_EOL
        . '<p class="coco_license">This program is free software:'
        . ' you can redistribute it and/or modify'
        . ' it under the terms of the GNU General Public License as published by'
        . ' the Free Software Foundation, either version 3 of the License, or'
        . ' (at your option) any later version.</p>' . PHP_EOL
        . '<p class="coco_license">This program is distributed'
        . ' in the hope that it will be useful,'
        . ' but <em>without any warranty</em>; without even the implied warranty of'
        . ' <em>merchantability</em> or <em>fitness for a particular purpose</em>.'
        . ' See the GNU General Public License for more details.</p>' . PHP_EOL
        . '<p class="coco_license"">You should have received a copy of the'
        . ' GNU General Public License along with this program.  If not, see'
        . ' <a href="http://www.gnu.org/licenses/">http://www.gnu.org/licenses/</a>'
        . '.</p>' . PHP_EOL;
}

/**
 * Returns the requirements information view.
 *
 * @return string (X)HTML.
 *
 * @global array The paths of system files and folders.
 * @global array The localization of the core.
 * @global array The localization of the plugins.
 */
function Coco_systemCheck()
{
    global $pth, $tx, $plugin_tx;

    define('COCO_PHP_VERSION', '4.3.10');
    $ptx = $plugin_tx['coco'];
    $imgdir = $pth['folder']['plugins'] . 'coco/images/';
    $ok = tag('img src="' . $imgdir . 'ok.png" alt="ok"');
    $warn = tag('img src="' . $imgdir . 'warn.png" alt="warning"');
    $fail = tag('img src="' . $imgdir . 'fail.png" alt="failure"');
    $o = '<h4>' . $ptx['syscheck_title'] . '</h4>'
        . (version_compare(PHP_VERSION, COCO_PHP_VERSION) >= 0 ? $ok : $fail)
        . '&nbsp;&nbsp;' . sprintf($ptx['syscheck_phpversion'], COCO_PHP_VERSION)
        . tag('br') . tag('br') . PHP_EOL;
    foreach (array('pcre') as $ext) {
        $o .= (extension_loaded($ext) ? $ok : $fail)
            . '&nbsp;&nbsp;' . sprintf($ptx['syscheck_extension'], $ext)
            . tag('br') . PHP_EOL;
    }
    $o .= tag('br')
        . (strtoupper($tx['meta']['codepage']) == 'UTF-8' ? $ok : $warn)
        . '&nbsp;&nbsp;' . $ptx['syscheck_encoding'] . tag('br') . PHP_EOL;
    $o .= (!get_magic_quotes_runtime() ? $ok : $fail)
        . '&nbsp;&nbsp;' . $ptx['syscheck_magic_quotes']
        . tag('br') . tag('br') . PHP_EOL;
    foreach (array('config/', 'css/', 'languages/') as $folder) {
        $folders[] = $pth['folder']['plugins'] . 'coco/' . $folder;
    }
    $folders[] = Coco_dataFolder();
    foreach ($folders as $folder) {
        $o .= (is_writable($folder) ? $ok : $warn)
            . '&nbsp;&nbsp;' . sprintf($ptx['syscheck_writable'], $folder)
            . tag('br') . PHP_EOL;
    }
    return $o;
}

/**
 * Deletes a co-content and all of its backups. Returns whether that succeeded,
 * and reports errors via e().
 *
 * @param string $name A co-content name.
 *
 * @return bool
 */
function Coco_delete($name)
{
    $fns = glob(Coco_dataFolder().'????????_??????_' . $name . '.htm');
    foreach ($fns as $fn) {
        if (!unlink($fn)) {
            e('cntdelete', 'backup', $fn);
            return false;
        }
    }
    if (!unlink(Coco_dataFolder() . $name . '.htm')) {
        e('cntdelete', 'file', $fn);
        return false;
    }
    return true;
}

/**
 * Returns the main administration view.
 *
 * @return string (X)HTML.
 *
 * @global string The script name.
 * @global array  The paths of system files and folders.
 * @global array  The localization of the core.
 * @global array  The localization of the plugins.
 */
function Coco_administration()
{
    global $sn, $pth, $tx, $plugin_tx;

    $ptx = $plugin_tx['coco'];
    if (isset($_POST['action']) && $_POST['action'] == 'delete') {
        Coco_delete(stsl($_POST['coco_name']));
    }
    $o = '<h1>Coco &ndash; ' . $ptx['menu_main'] . '</h1>' . PHP_EOL
        . '<div id="coco_admin_cocos">' . PHP_EOL;
    $o .= '<ul>' . PHP_EOL;
    foreach (Coco_cocos() as $coco) {
        $url = $sn . '?&amp;coco&amp;admin=plugin_main';
        $message = addcslashes(sprintf($ptx['confirm_delete'], $coco), "\n\r\t\\");
        $message = Coco_hsc($message);
        $js = 'return confirm(\'' . $message . '\')';
        $alt = ucfirst($tx['action']['delete']); // FIXME
        $o .= '<li>'
            . '<form action="' . $url . '" method="POST" onsubmit="' . $js . '">'
            . tag('input type="hidden" name="action" value="delete"')
            . tag('input type="hidden" name="coco_name" value="' . $coco . '"')
            . tag(
                'input type="image" src="' . $pth['folder']['plugins']
                . 'coco/images/delete.png" alt="' . $alt . '" title="' . $alt . '"'
            )
            . '</form>' . $coco . '</li>' . PHP_EOL;
    }
    $o .= '</ul>' . PHP_EOL . '</div>' . PHP_EOL;
    return $o;
}

/*
 * Register plugin menu items.
 */
if (function_exists('XH_registerStandardPluginMenuItems')) {
    XH_registerStandardPluginMenuItems(true);
}

/*
 * Handle the plugin administration.
 */
if (function_exists('XH_wantsPluginAdministration')
    && XH_wantsPluginAdministration('coco')
    || isset($coco) && $coco == 'true'
) {
    $o .= print_plugin_admin('on');
    switch ($admin) {
    case '':
        $o .= Coco_version() . tag('hr') . Coco_systemCheck();
        break;
    case 'plugin_main':
        $o .= Coco_administration();
        break;
    default:
        $o .= plugin_admin_common($action, $admin, $plugin);
    }
}

?>
