<?php

// utf8-marker: äöüß


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
    $text = mb_strtolower(strip_tags($text), 'UTF-8'); // TODO: entities?
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
 * @param array $words
 * @return array
 */
function coco_search_content($name, $words) {
    global $c, $cl, $cf;

    $ta = array();
    for ($i = 0; $i < $cl; $i++) {
        if (!hide($i) || $cf['hidden']['pages_search'] == 'true') {
	    $text = is_null($name) ? $c[$i] : coco_fetch_complete($name, $i);
	    if (coco_search($words, $text)) {$ta[] = $i;}
	}
    }
    return $ta;
}


function coco_search_results() {
    global $search, $h, $u, $tx, $plugin_cf;

    $o = '';
    $search = stsl($search);
    $words = preg_split('/\s+/isu', $search, NULL, PREG_SPLIT_NO_EMPTY);
    $ta = coco_search_content(NULL, $words);
    foreach (explode(',', $plugin_cf['coco']['search']) as $name) {
	$ta = array_merge($ta, coco_search_content($name, $words));
    }
    $ta = array_unique($ta);
    sort($ta);
    $o .= '<h1>'.$tx['search']['result'].'</h1>'."\n"
	    .'<p>"'.htmlspecialchars($search).'" ';
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
            $o .= '<li><a href="'.$sn.'?'.$u[$i].'&amp;search='.urlencode($words).'">'.$h[$i].'</a></li>'."\n";
        }
        $o .= '</ul>'."\n";
    }
    return $o;
}


$title = $tx['title']['search'];
$o .= coco_search_results();

?>
