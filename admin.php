<?php

/**
 * Back-End of Coco_XH.
 *
 * Copyright (c) 2012 Christoph M. Becker (see license.txt)
 */


// utf8-marker: äöüß


if (!defined('CMSIMPLE_XH_VERSION')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}


define('COCO_VERSION', '1alpha2');


/**
 * Returns the plugin's version information view.
 *
 * @return string  The (X)HTML.
 */
function coco_version() {
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
    $htm = tag('hr').'<h4>'.$ptx['syscheck_title'].'</h4>'
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


///**
// * Joins multiple content files to one.
// *
// * @return void
// */
//function coco_join() {
//    global $cl, $l, $h, $pd_router;
//
//    $cnt = '';
//    for ($i = 0; $i < $cl; $i++) {
//	$pd = $pd_router->find_page($i);
//	$id = !empty($pd['coco_id']) ? $pd['coco_id'] : '';
//	$cnt .= '<h'.$l[$i].' id="'.$id.'">'.$h[$i].'</h'.$l[$i].'>'."\n"
//		//.'<!-- coco_id '.'-->'."\n"
//		.coco_fetch('main', $i);
//    }
//    $fn = coco_data_folder().'main.htm';
//    if (($fp = fopen($fn, 'w')) === FALSE
//	    || fwrite($fp, $cnt) === FALSE) {
//	e('cntwriteto', 'file', $fn);
//    }
//    if ($fp !== FALSE) {fclose($fp);}
//}


//function coco_import_content() { //TODO: must read content again (may be altered by other plugins already)
//    global $c, $cl, $cf, $pd_router;
//
//    for ($i = 0; $i < $cl; $i++) {
//	preg_match('/^(.*<\/h[1-'.$cf['menu']['levels'].']>)(.*)$/isu', $c[$i], $matches);
//	$heading = $matches[1]."\n";
//	$body = ltrim($matches[2]);
//	if (count($matches)!=3) {var_dump($c[$i]);}
//	coco_save('main', $i, $body);
//    }
//
//}
//
//
//function coco_export_content() {
//
//}


/**
 * Returns the main administration view.
 *
 * @return string  (X)HTML
 */
function coco_admin_main() {
    global $sn;

    $url = $sn.'?coco&amp;admin=plugin_main&amp;action=';
    $o = '<div>'
	    .'<a href="'.$url.'import_content">Content -> Co-content</a>'
	    .'<a href="'.$url.'export_content">Co-content -> Content</a>'
	    .'<a href="'.$url.'join">Join</a>'
	    .'</div>';
    return $o;
}


/**
 * Handle the plugin administration.
 */
if (!empty($coco)) {
    $o .= print_plugin_admin('off');
    switch ($admin) {
	case '':
	    $o .= coco_version().coco_system_check();
	    break;
	//case 'plugin_main':
	//    switch ($action) {
	//	case 'import_content':
	//	    $o .= coco_import_content();
	//	    break;
	//	case 'join':
	//	    $o .= coco_join();
	//	    break;
	//	default:
	//	    $o .= coco_admin_main();
	//    }
	//    break;
	default:
	    $o .= plugin_admin_common($action, $admin, $plugin);
    }
}

?>
