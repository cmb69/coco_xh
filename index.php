<?php

/**
 * Front-End of Coco_XH.
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
function coco_data_folder() {
    global $pth, $sl, $cf, $plugin_cf;

    $pcf = $plugin_cf['coco'];

    if ($pcf['folder_data'] == '') {
	$fn = $pth['folder']['plugins'].'coco/data/';
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
//function coco_fetch($name, $i) {
//    global $pd_router;
//
//    $text = '';
//    $pd = $pd_router->find_page($i);
//    if (empty($pd['coco_id'])) {
//	return $text;
//    } else {
//	$fn = coco_data_folder().$pd['coco_id'].(empty($name) ? '' : '-').$name.'.htm';
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
function coco_fetch_complete($name, $i) { // TODO: cache last content file for search
    global $cf, $pd_router;

    $pd = $pd_router->find_page($i);
    if (empty($pd['coco_id'])) {return '';}
    $fn = coco_data_folder().$name.'.htm';
    if (!is_readable($fn) || ($text = file_get_contents($fn)) === FALSE) {
	e('cntopen', 'file', $fn);
	return FALSE;
    }
    $ml = $cf['menu']['levels'];
    preg_match('/<h[1-'.$ml.'].*?id="'.$pd['coco_id'].'".*?>.*?<\/h[1-'.$ml.']>'
	    .'(.*?)<h[1-'.$ml.']/isu', $text, $matches);
    return trim($matches[1]);
}


///**
// * Saves $text as content of page $i.
// * (version for multiple content files)
// *
// * @return void
// */
// function coco_save($name, $i, $text) {
//    global $pd_router;
//
//    $pd = $pd_router->find_page($i);
//    if (empty($pd['coco_id'])) {
//	$pd['coco_id'] = uniqid();
//	$pd_router->update($i, $pd);
//    }
//    $fn = coco_data_folder().$pd['coco_id'].(empty($name) ? '' : '-').$name.'.htm';
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
function coco_save_complete($name, $i, $text) {
    global $cl, $l, $h, $cf, $pd_router;

    $fn = coco_data_folder().$name.'.htm';
    $old = is_readable($fn) ? file_get_contents($fn) : '';
    $ml = $cf['menu']['levels'];
    $cnt = '<html>'."\n".'<body>'."\n";
    for ($j = 0; $j < $cl; $j++) {
	$pd = $pd_router->find_page($j);
	if (empty($pd['coco_id'])) {
	    $pd['coco_id'] = uniqid();
	    $pd_router->update($j, $pd);
	}
	$cnt .= '<h'.$l[$j].' id="'.$pd['coco_id'].'">'.$h[$j].'</h'.$l[$j].'>'."\n";
	if ($j == $i) {
	    $text = trim(preg_replace('/<h'.$ml.'.*?>.*?<\/h'.$ml.'>/isu', '', $text));
	    if (!empty($text)) {$cnt .= $text."\n";}
	} else {
	    preg_match('/<h[1-'.$ml.'].*?id="'.$pd['coco_id'].'".*?>.*?<\/h[1-'.$ml.']>'
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
function coco($name, $config = FALSE, $height = '100%') {
    global $adm, $edit, $s, $cl, $e, $tx, $plugin_tx;

    if (!preg_match('/^[a-z_0-9]+$/su', $name)) {
	return '<div class="cmsimplecore_warning">'.$plugin_tx['coco']['error_invalid_name'].'</div>'."\n";
    }
    if ($s < 0 || $s >= $cl) {return '';}
    $o = '';
    if ($adm && $edit) {
	if (isset($_POST['coco_text_'.$name])) {
	    coco_save_complete($name, $s, stsl($_POST['coco_text_'.$name]));
	}
	$id = 'coco_text_'.$name;
	$style = 'width:100%; height:'.$height;
	$er = function_exists('editor_replace') ? editor_replace($id, $config) : FALSE;
	$o .= '<form action="" method="POST">'."\n"
		.'<textarea id="'.$id.'" name="coco_text_'.$name.'" style="'.$style.'">'.coco_fetch_complete($name, $s).'</textarea>'."\n"
		.(!$er ? tag('input type="submit" class="submit" value="'.ucfirst($tx['action']['save']).'"') : '')
		.'</form>'."\n";
	if ($er) {
	    $o .= '<script type="text/javascript">'."\n".'/* <![CDATA[ */'."\n"
		    .$er."\n".'/* ]]> */'."\n".'</script>'."\n";
	}
    } else {
	$text = evaluate_scripting(coco_fetch_complete($name, $s));
	if (isset($_GET['search'])) {
	    $words = explode(',', urldecode($_GET['search']));
	    $words = array_map(create_function('$w', 'return "&".$w."(?!([^<]+)?>)&isU";'), $words);
	    $text = preg_replace($words, '<span class="highlight_search">\\0</span>', $text);
	}
	$o .= $text;
    }
    return $o;
}


/**
 * Register the coco id in the page data.
 */
$pd_router->add_interest('coco_id');


/**
 * Include the editor in the <head>.
 *
 */
//if ($plugin_cf['coco']['enabled'] && $adm && $edit && function_exists('include_editor')) {
//    include_editor();
//}


//if ($f == 'search') {
//    $o .= 'Nix gefunden!';
//    $o .= print_r(coco_search_content(stsl($_POST['search'])), TRUE);
//    $f = '';
//}

?>
