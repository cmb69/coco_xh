<?php

/**
 * Search functionality of Coco_XH.
 *
 * Copyright (c) 2012 Christoph M. Becker (see license.txt)
 */


if (!defined('CMSIMPLE_XH_VERSION')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}


/**
 * Returns wether all $words are contained in $text.
 *
 * @param array $words
 * @param string $text
 * @return bool
 */
function coco_search($words, $text) {
    $text = mb_strtolower(html_entity_decode(strip_tags(evaluate_scripting($text)), ENT_QUOTES, 'UTF-8'), 'UTF-8');
    foreach ($words as $word) {
	if (strpos($text, mb_strtolower($word, 'UTF-8')) === FALSE) {
	    return FALSE;
	}
    }
    return TRUE;
}


/**
 * Returns a list of all pages that contain all $words in co-content $name.
 * If $name === NULL the main content will be searched.
 *
 * @param string $name  The name of the co-content.
 * @param array $words
 * @return array
 */
function coco_search_content($name, $words) {
    global $c, $cl, $cf;

    $ta = array();
    for ($i = 0; $i < $cl; $i++) {
        if (!hide($i) || $cf['hidden']['pages_search'] == 'true') {
	    $text = is_null($name) ? $c[$i] : coco_get($name, $i);
	    if (coco_search($words, $text)) {$ta[] = $i;}
	}
    }
    return $ta;
}


/**
 * Returns the search results view.
 *
 * @return string  The (X)HTML.
 */
function coco_search_results() {
    global $search, $sn, $h, $u, $tx, $plugin_cf;

    $o = '';
    $words = preg_split('/\s+/isu', stsl($search), NULL, PREG_SPLIT_NO_EMPTY);
    $ta = coco_search_content(NULL, $words);
    foreach (coco_cocos() as $name) {
	$ta = array_merge($ta, coco_search_content($name, $words));
    }
    $ta = array_unique($ta);
    sort($ta);
    $o .= '<h1>'.$tx['search']['result'].'</h1>'."\n"
	    .'<p>"'.htmlspecialchars($search, ENT_QUOTES, 'UTF-8').'" ';
    if (count($ta) == 0) {
	$o .= $tx['search']['notfound'];
    }
    else {
	$o .= $tx['search']['foundin'].' '.count($ta).' ';
	if (count($ta) > 1) {
	    $o .= $tx['search']['pgplural'];
	} else {
	    $o .= $tx['search']['pgsingular'];
	}
	$o .= ':';
    }
    $o .= '</p>'."\n";
    if (count($ta) > 0) {
        $o .= '<ul>'."\n";
	$words = implode(',', $words);
        foreach ($ta as $i) {
            $o .= '<li>'.a($i, '&amp;search='.urlencode($words)).$h[$i].'</a></li>'."\n";
        }
        $o .= '</ul>'."\n";
    }
    return $o;
}


/**
 * Search.
 */
$title = $tx['title']['search'];
$o .= coco_search_results();

?>
