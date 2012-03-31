<?php

/**
 * Back-End of Coco_XH.
 *
 * Copyright (c) 2012 Christoph M. Becker (see license.txt)
 */


if (!defined('CMSIMPLE_XH_VERSION')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}


define('COCO_VERSION', '1beta1');


/**
 * Returns the plugin's version information view.
 *
 * @return string  The (X)HTML.
 */
function coco_version() { // TODO plugin icon
    return '<h1><a href="http://3-magi.net/?CMSimple_XH/Coco_XH">Coco_XH</a></h1>'."\n"
	    .'<p>Version: '.COCO_VERSION.'</p>'."\n"
	    .'<p>Copyright &copy; 2012 <a href="http://3-magi.net">Christoph M. Becker</a></p>'."\n"
	    .'<p style="text-align: justify">This program is free software: you can redistribute it and/or modify'
	    .' it under the terms of the GNU General Public License as published by'
	    .' the Free Software Foundation, either version 3 of the License, or'
	    .' (at your option) any later version.</p>'."\n"
	    .'<p style="text-align: justify">This program is distributed in the hope that it will be useful,'
	    .' but WITHOUT ANY WARRANTY; without even the implied warranty of'
	    .' MERCHAN&shy;TABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the'
	    .' GNU General Public License for more details.</p>'."\n"
	    .'<p style="text-align: justify">You should have received a copy of the GNU General Public License'
	    .' along with this program.  If not, see'
	    .' <a href="http://www.gnu.org/licenses/">http://www.gnu.org/licenses/</a>.</p>'."\n";
}


/**
 * Returns the requirements information view.
 *
 * @return string  The (X)HTML.
 */
function coco_system_check() { // RELEASE-TODO
    global $pth, $tx, $plugin_tx;

    define('COCO_PHP_VERSION', '4.3.0');
    $ptx = $plugin_tx['coco'];
    $imgdir = $pth['folder']['plugins'].'coco/images/';
    $ok = tag('img src="'.$imgdir.'ok.png" alt="ok"');
    $warn = tag('img src="'.$imgdir.'warn.png" alt="warning"');
    $fail = tag('img src="'.$imgdir.'fail.png" alt="failure"');
    $htm = '<h4>'.$ptx['syscheck_title'].'</h4>'
	    .(version_compare(PHP_VERSION, COCO_PHP_VERSION) >= 0 ? $ok : $fail)
	    .'&nbsp;&nbsp;'.sprintf($ptx['syscheck_phpversion'], COCO_PHP_VERSION)
	    .tag('br').tag('br')."\n";
    foreach (array('pcre') as $ext) {
	$htm .= (extension_loaded($ext) ? $ok : $fail)
		.'&nbsp;&nbsp;'.sprintf($ptx['syscheck_extension'], $ext).tag('br')."\n";
    }
    $htm .= tag('br').(strtoupper($tx['meta']['codepage']) == 'UTF-8' ? $ok : $warn)
	    .'&nbsp;&nbsp;'.$ptx['syscheck_encoding'].tag('br')."\n";
    $htm .= (!get_magic_quotes_runtime() ? $ok : $fail)
	    .'&nbsp;&nbsp;'.$ptx['syscheck_magic_quotes'].tag('br').tag('br')."\n";
    foreach (array('config/', 'css/', 'languages/') as $folder) {
	$folders[] = $pth['folder']['plugins'].'coco/'.$folder;
    }
    $folders[] = coco_data_folder();
    foreach ($folders as $folder) {
	$htm .= (is_writable($folder) ? $ok : $warn)
		.'&nbsp;&nbsp;'.sprintf($ptx['syscheck_writable'], $folder).tag('br')."\n";
    }
    return $htm;
}


/**
 * Deletes the co-content $name and all of its backups.
 * Returns wether that succeeded, and report errors via e().
 *
 * return bool
 */
function coco_delete($name) {
    $fns = glob(coco_data_folder().'????????_??????_'.$name.'.htm');
    foreach ($fns as $fn) {
	if (!unlink($fn)) {
	    e('cntdelete', 'backup', $fn);
	    return FALSE;
	}
    }
    if (!unlink(coco_data_folder().$name.'.htm')) {
	e('cntdelete', 'file', $fn);
	return FALSE;
    }
    return TRUE;
}


/**
 * Returns the main administration view.
 *
 * @return string  The (X)HTML.
 */
function coco_admin_main() {
    global $sn, $pth, $tx, $plugin_tx;

    $ptx = $plugin_tx['coco'];
    if (isset($_POST['action']) && $_POST['action'] == 'delete') {
	coco_delete(stsl($_POST['coco_name']));
    }
    $o = '<div id="coco_admin_cocos">'."\n".'<h1>'.$ptx['title_cocos'].'</h1>'."\n";
    $o .= '<ul>'."\n";
    foreach (coco_cocos() as $coco) {
	$url = $sn.'?&amp;coco&amp;admin=plugin_main';
	$msg = htmlspecialchars(addcslashes(sprintf($ptx['confirm_delete'], $coco), "\n\r\t\\"), ENT_QUOTES);
	$js = 'return confirm(\''.$msg.'\')';
	$alt = ucfirst($tx['action']['delete']);
	$o .= '<li><form action="'.$url.'" method="POST" onsubmit="'.$js.'">'
		.tag('input type="hidden" name="action" value="delete"')
		.tag('input type="hidden" name="coco_name" value="'.$coco.'"')
		.tag('input type="image" src="'.$pth['folder']['plugins'].'coco/images/delete.png"'
		    .' alt="'.$alt.'" title="'.$alt.'"')
		.'</form>'.$coco.'</li>'."\n";
    }
    $o .= '</ul>'."\n".'</div>'."\n";
    return $o; // TODO: info that no cocos are available
}


/**
 * Handle the plugin administration.
 */
if (!empty($coco)) {
    $o .= print_plugin_admin('on');
    switch ($admin) {
	case '':
	    $o .= coco_version().tag('hr').coco_system_check();
	    break;
	case 'plugin_main':
	    $o .= coco_admin_main();
	    break;
	default:
	    $o .= plugin_admin_common($action, $admin, $plugin);
    }
}

?>
