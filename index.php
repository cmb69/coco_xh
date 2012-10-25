<?php

/**
 * Front-End of Coco_XH.
 *
 * Copyright (c) 2012 Christoph M. Becker (see license.txt)
 */


if (!defined('CMSIMPLE_XH_VERSION')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}


define('COCO_VERSION', '1rc2');


/**
 * Compatibility with CMSimple_XH < 1.5
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


/**
 * Returns all available co-contents.
 *
 * @return array
 */
function coco_cocos() {
    $cocos = glob(coco_data_folder().'*.htm');
    $cocos = array_map(create_function('$fn', 'return basename($fn, \'.htm\');'), $cocos);
    $cocos = array_filter($cocos, create_function('$fn', 'return !preg_match(\'/^\d{8}_\d{6}_/\', $fn);'));
    return $cocos;
}


/**
 * Returns the co-content of page $i.
 *
 * @param string $name  The name of the co-content.
 * @param int $i  The number of the page.
 * @return string
 */
function coco_get($name, $i) {
    global $cf, $pd_router;
    static $curname = NULL;
    static $text = NULL;

    $pd = $pd_router->find_page($i);
    if (empty($pd['coco_id'])) {return '';}
    if ($name != $curname) {
	$curname = $name;
	$fn = coco_data_folder().$name.'.htm';
	if (!is_readable($fn) || ($text = file_get_contents($fn)) === FALSE) {
	    e('cntopen', 'file', $fn);
	    return FALSE;
	}
    }
    $ml = $cf['menu']['levels'];
    preg_match('/<h[1-'.$ml.'].*?id="'.$pd['coco_id'].'".*?>.*?<\/h[1-'.$ml.']>'
	    .'(.*?)<(?:h[1-'.$ml.']|\/body)/isu', $text, $matches);
    return !empty($matches[1]) ? trim($matches[1]) : '';
}


/**
 * Saves $text as co-content of page $i.
 *
 * @param string $name  The name of the co-content.
 * @param int $i  The number of the page.
 * @param string $text  The new co-content of this page.
 * @return void
 */
function coco_set($name, $i, $text) {
    global $pth, $cl, $l, $h, $cf, $pd_router;

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
	    $text = preg_replace('/(<\/?h)[1-' . $ml . ']/is', '${1}' . ($ml + 1), $text);
	    if (!empty($text)) {$cnt .= $text."\n";}
	} else {
	    preg_match('/<h[1-'.$ml.'].*?id="'.$pd['coco_id'].'".*?>.*?<\/h[1-'.$ml.']>'
		    .'(.*?)<(?:h[1-'.$ml.']|\/body)/isu', $old, $matches);
	    $cnt .= isset($matches[1]) && ($match = trim($matches[1])) != '' ? $match."\n" : '';
	}
    }
    $cnt .= '</body>'."\n".'</html>'."\n";
    if (($fp = fopen($fn, 'w')) !== FALSE
	    && fwrite($fp, $cnt) !== FALSE) {
	touch($pth['file']['content']);
    } else {
	e('cntwriteto', 'file', $fn);
    }
    if ($fp !== FALSE) {fclose($fp);}
}


/**
 * Creates new backups of all co-contents and deletes superfluous ones.
 * Returns the success messages. Errors are signalled via e().
 *
 * @return string
 */
function coco_backup() {
    global $cf, $tx, $backupDate;

    $dir = coco_data_folder();
    if (!isset($backupDate)) {$backupDate = date("Ymd_His");}
    $o = '';
    foreach (coco_cocos() as $coco) {
	$fn = $dir.$backupDate.'_'.$coco.'.htm';
	if (copy($dir.$coco.'.htm', $fn)) {
	    $o .= '<p>'.ucfirst($tx['filetype']['backup']).' '.$fn.' '.$tx['result']['created'].'</p>'."\n";
	    $bus = glob($dir.'????????_??????_'.$coco.'.htm');
	    for ($i = 0; $i < count($bus) - $cf['backup']['numberoffiles']; $i++) {
		if (unlink($bus[$i])) {
		    $o .= '<p>'.ucfirst($tx['filetype']['backup']).' '.$bus[$i]
			    .' '.$tx['result']['deleted'].'</p>'."\n";
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
 * Returns the co-content view depending on the mode.
 *
 * @access public
 * @param string $name  The name of the co-content.
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
	    coco_set($name, $s, stsl($_POST['coco_text_'.$name]));
	}
	$id = 'coco_text_'.$name;
	$style = 'width:100%; height:'.$height;
	$er = function_exists('editor_replace') ? editor_replace($id, $config) : FALSE;
	$o .= '<form action="" method="POST">'."\n"
		.'<textarea id="'.$id.'" name="coco_text_'.$name.'" style="'.$style.'">'
		.htmlspecialchars(coco_get($name, $s), ENT_COMPAT, 'UTF-8').'</textarea>'."\n"
		.(!$er ? tag('input type="submit" class="submit" value="'.ucfirst($tx['action']['save']).'"') : '')
		.'</form>'."\n";
	if ($er) {
	    $o .= '<script type="text/javascript">'."\n".'/* <![CDATA[ */'."\n"
		    .$er."\n".'/* ]]> */'."\n".'</script>'."\n";
	}
    } else {
	$text = evaluate_scripting(coco_get($name, $s));
	if (isset($_GET['search'])) {
	    $words = explode(',', htmlspecialchars(urldecode($_GET['search']), ENT_NOQUOTES, 'UTF-8'));
	    $words = array_map(create_function('$w', 'return "/".preg_quote($w, "/")."(?!([^<]+)?>)/isU";'), $words);
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
 * Create and delete backups.
 */
if ($logout && $_COOKIE['status'] == 'adm' && logincheck()) {
    $o .= coco_backup();
}

?>
