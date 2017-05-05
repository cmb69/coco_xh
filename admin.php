<?php

/**
 * Administration of Coco_XH.
 *
 * PHP versions 4 and 5
 *
 * @category  CMSimple_XH
 * @package   Coco
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2012-2017 Christoph M. Becker <http://3-magi.net>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
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
    global $pth;

    $view = new Coco\View('info');
    $view->logo = $pth['folder']['plugins'] . 'coco/coco.png';
    $view->version = COCO_VERSION;
    return (string) $view;
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

    define('COCO_PHP_VERSION', '5.4.0');
    $ptx = $plugin_tx['coco'];
    $imgdir = $pth['folder']['plugins'] . 'coco/images/';
    $ok = tag('img src="' . $imgdir . 'ok.png" alt="ok"');
    $warn = tag('img src="' . $imgdir . 'warn.png" alt="warning"');
    $fail = tag('img src="' . $imgdir . 'fail.png" alt="failure"');
    $o = '<h4>' . $ptx['syscheck_title'] . '</h4>'
        . (version_compare(PHP_VERSION, COCO_PHP_VERSION) >= 0 ? $ok : $fail)
        . '&nbsp;&nbsp;' . sprintf($ptx['syscheck_phpversion'], COCO_PHP_VERSION)
        . tag('br') . PHP_EOL;
    foreach (array() as $ext) {
        $o .= (extension_loaded($ext) ? $ok : $fail)
            . '&nbsp;&nbsp;' . sprintf($ptx['syscheck_extension'], $ext)
            . tag('br') . PHP_EOL;
    }
    $o .= tag('br')
        . (strtoupper($tx['meta']['codepage']) == 'UTF-8' ? $ok : $warn)
        . '&nbsp;&nbsp;' . $ptx['syscheck_encoding'] . tag('br') . tag('br') . PHP_EOL;
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
    global $_XH_csrfProtection;

    $_XH_csrfProtection->check();
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
    global $sn, $pth, $tx, $plugin_tx, $_XH_csrfProtection;

    if (isset($_POST['action']) && $_POST['action'] == 'delete') {
        Coco_delete($_POST['coco_name']);
    }
    $view = new Coco\View('admin');
    $view->csrfTokenInput = new Coco\HtmlString($_XH_csrfProtection->tokenInput());
    $view->url = "$sn?&amp;coco&amp;admin=plugin_main";
    $view->deleteIcon = "{$pth['folder']['plugins']}coco/images/delete.png";
    $view->alt = ucfirst($tx['action']['delete']);
    $cocos = [];
    foreach (Coco_cocos() as $coco) {
        $message = addcslashes(sprintf($plugin_tx['coco']['confirm_delete'], $coco), "\n\r\t\\");
        $cocos[] = (object) ['name' => $coco, 'message' => $message];
    }
    $view->cocos = $cocos;
    return (string) $view;
}

XH_registerStandardPluginMenuItems(true);

/*
 * Handle the plugin administration.
 */
if (XH_wantsPluginAdministration('coco')) {
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
