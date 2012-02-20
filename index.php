<?php

/**
 * Front-End of Also_XH.
 *
 * Copyright (c) 2012 Christoph M. Becker (see license.txt)
 */

// utf8-marker: äöüß


if (!defined('CMSIMPLE_XH_VERSION')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}


/**
 * Compatibility for CMSimple_XH < 1.5
 */
if (!function_exists('evaluate_scripting')) {
    function evaluate_scripting($text) {
	return $text;
    }
}


/**
 * Returns the path of the data folder.
 *
 * @return string
 */
function also_data_folder() {
    global $pth, $sl, $cf, $plugin_cf;

    $pcf = $plugin_cf['also'];

    if ($pcf['folder_data'] == '') {
	$fn = $pth['folder']['plugins'].'also/data/';
    } else {
	$fn = $pth['folder']['base'].$pcf['folder_data'];
    }
    if (substr($fn, -1) != '/') {$fn .= '/';}
    if (file_exists($fn)) {
	if (!is_dir($fn)) {e('cntopen', 'folder', $fn);}
    } else {
	if (!mkdir($fn, 0777, TRUE)) {e('cntwriteto', 'folder', $fn);}
    }
    if ($sl != $cf['language']['default']) {
	$fn .= $sl.'/';
	if (file_exists($fn)) {
	    if (!is_dir($fn)) {e('cntopen', 'folder', $fn);}
	} else {
	    if (!mkdir($fn, 0777, TRUE)) {e('cntwriteto', 'folder', $fn);}
	}
    }
    return $fn;
}


///**
// * Returns the content of page $i.
// * (version for multiple content files)
// *
// * @return string
// */
//function also_fetch($name, $i) {
//    global $pd_router;
//
//    $text = '';
//    $pd = $pd_router->find_page($i);
//    if (empty($pd['also_id'])) {
//	return $text;
//    } else {
//	$fn = also_data_folder().$pd['also_id'].(empty($name) ? '' : '-').$name.'.htm';
//	if (!is_readable($fn) || ($text = file_get_contents($fn)) === FALSE) {
//	    e('cntopen', 'file', $fn);
//	}
//	return $text;
//    }
//}


/**
 * Returns the content of page $i.
 * (version for a single content file)
 *
 * @param string $name  The name of the content.
 * @param int $i  The number of the page.
 * @return string
 */
function content_fetch_complete($name, $i) { // TODO: cache last content file for search
    global $cf, $pd_router;

    $pd = $pd_router->find_page($i);
    if (empty($pd['also_id'])) {return '';}
    $fn = also_data_folder().$name.'.htm';
    if (!is_readable($fn) || ($text = file_get_contents($fn)) === FALSE) {
	e('cntopen', 'file', $fn);
	return FALSE;
    }
    $ml = $cf['menu']['levels'];
    preg_match('/<h[1-'.$ml.'].*?id="'.$pd['also_id'].'".*?>.*?<\/h[1-'.$ml.']>'
	    .'(.*?)<h[1-'.$ml.']/isu', $text, $matches);
    return trim($matches[1]);
}


///**
// * Saves $text as content of page $i.
// * (version for multiple content files)
// *
// * @return void
// */
// function also_save($name, $i, $text) {
//    global $pd_router;
//
//    $pd = $pd_router->find_page($i);
//    if (empty($pd['also_id'])) {
//	$pd['also_id'] = uniqid();
//	$pd_router->update($i, $pd);
//    }
//    $fn = also_data_folder().$pd['also_id'].(empty($name) ? '' : '-').$name.'.htm';
//    if (($fp = fopen($fn, 'w')) === FALSE
//	    || fwrite($fp, $text) === FALSE) {
//	e('cntwriteto', 'file', $fn);
//    }
//    if ($fp !== FALSE) {fclose($fp);}
//}


/**
 * Saves $text as content of page $i.
 * (version for a single content file)
 *
 * @param string $name  The name of the content.
 * @param int $i  The number of the page.
 * @param string $text  The new content of this page.
 * @return void
 */
function content_save_complete($name, $i, $text) {
    global $cl, $l, $h, $cf, $pd_router;

    $fn = also_data_folder().$name.'.htm';
    $old = is_readable($fn) ? file_get_contents($fn) : '';
    $ml = $cf['menu']['levels'];
    $cnt = '<html>'."\n".'<body>'."\n";
    for ($j = 0; $j < $cl; $j++) {
	$pd = $pd_router->find_page($j);
	if (empty($pd['also_id'])) {
	    $pd['also_id'] = uniqid();
	    $pd_router->update($j, $pd);
	}
	$cnt .= '<h'.$l[$j].' id="'.$pd['also_id'].'">'.$h[$j].'</h'.$l[$j].'>'."\n";
	if ($j == $i) {
	    $text = trim(preg_replace('/<h'.$ml.'.*?>.*?<\/h'.$ml.'>/isu', '', $text));
	    if (!empty($text)) {$cnt .= $text."\n";}
	} else {
	    preg_match('/<h[1-'.$ml.'].*?id="'.$pd['also_id'].'".*?>.*?<\/h[1-'.$ml.']>'
		    .'(.*?)<h[1-'.$ml.']/isu', $old, $matches);
	    $cnt .= isset($matches[1]) && ($match = trim($matches[1])) != '' ? $match."\n" : '';
	}
    }
    $cnt .= '</body>'."\n".'</html>'."\n";
    if (($fp = fopen($fn, 'w')) === FALSE
	    || fwrite($fp, $cnt)) {
	e('cntwriteto', 'file', $fn);
    }
    if ($fp !== FALSE) {fclose($fp);}
}


/**
 * Returns the content view depending on the mode.
 *
 * @access public
 * @param string $name  The name of the content.
 * @param string $config  The config of the editor.
 * @param string $height  The height of the editor as CSS length.
 * @return string  The (X)HTML
 */
function also($name, $config = FALSE, $height = '100%') {
    global $adm, $edit, $s, $cl, $e, $tx, $plugin_tx;

    if (!preg_match('/^[a-z_0-9]+$/su', $name)) {
	return '<div class="cmsimplecore_warning">'.$plugin_tx['also']['error_invalid_name'].'</div>'."\n";
    }
    if ($s < 0 || $s >= $cl) {return '';}
    $o = '';
    if ($adm && $edit) {
	if (isset($_POST['also_text_'.$name])) {
	    content_save_complete($name, $s, stsl($_POST['also_text_'.$name]));
	}
	$id = 'also_text_'.$name;
	$style = 'width:100%; height:'.$height;
	$er = function_exists('editor_replace') ? editor_replace($id, $config) : FALSE;
	$o .= '<form action="" method="POST">'."\n"
		.'<textarea id="'.$id.'" name="also_text_'.$name.'" style="'.$style.'">'.content_fetch_complete($name, $s).'</textarea>'."\n"
		.(!$er ? tag('input type="submit" class="submit" value="'.ucfirst($tx['action']['save']).'"') : '')
		.'</form>'."\n";
	if ($er) {
	    $o .= '<script type="text/javascript">'."\n".'/* <![CDATA[ */'."\n"
		    .$er."\n".'/* ]]> */'."\n".'</script>'."\n";
	}
    } else {
	$o .= evaluate_scripting(content_fetch_complete($name, $s));
    }
    return $o;
}


/**
 * Includes the editor in the <head>.
 *
 * @access public
 * @return void
 */
function also_enable() {
    global $adm, $edit;

    if ($adm && $edit && function_exists('include_editor')) {
	include_editor();
    }
}


/**
 * Register the also id in the page data.
 */
$pd_router->add_interest('also_id');


//if ($f == 'search') {
//    $o .= 'Nix gefunden!';
//    $f = '';
//}

?>
