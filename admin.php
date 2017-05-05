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
 * Returns the plugin's information view.
 *
 * @return string (X)HTML.
 *
 * @global array The paths of system files and folders.
 * @global array The localization of the plugins.
 */
function Coco_info()
{
    global $pth;

    $view = new Coco\View('info');
    $view->logo = $pth['folder']['plugins'] . 'coco/coco.png';
    $view->version = COCO_VERSION;
    $view->checks = (new Coco\SystemCheckService)->getChecks();
    return (string) $view;
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
        $o .= Coco_info();
        break;
    case 'plugin_main':
        $o .= Coco_administration();
        break;
    default:
        $o .= plugin_admin_common($action, $admin, $plugin);
    }
}

?>
