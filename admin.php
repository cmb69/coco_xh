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

XH_registerStandardPluginMenuItems(true);

/*
 * Handle the plugin administration.
 */
if (XH_wantsPluginAdministration('coco')) {
    $o .= print_plugin_admin('on');
    switch ($admin) {
        case '':
            ob_start();
            (new Coco\InfoController)->defaultAction();
            $o .= ob_get_clean();
            break;
        case 'plugin_main':
            $temp = new Coco\MainAdminController;
            ob_start();
            switch ($action) {
                case 'delete':
                    $temp->deleteAction();
                    break;
                default:
                    $temp->defaultAction();
            }
            $o .= ob_get_clean();
            break;
        default:
            $o .= plugin_admin_common($action, $admin, $plugin);
    }
}
