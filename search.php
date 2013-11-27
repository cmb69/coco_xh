<?php

/**
 * Search functionality of Coco_XH.
 *
 * PHP versions 4 and 5
 *
 * @category  CMSimple_XH
 * @package   Coco
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2012-2013 Christoph M. Becker <http://3-magi.net>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://3-magi.net/?CMSimple_XH/Coco_XH
 */

/*
 * Prevent direct access.
 */
if (!defined('CMSIMPLE_XH_VERSION')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

/**
 * Returns whether all words are contained in a text.
 *
 * @param array  $words An array of words.
 * @param string $text  A text to search in.
 * 
 * @return bool
 */
function Coco_search($words, $text)
{
    $text = strip_tags(Coco_evaluateScripting($text));
    $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
    $text = mb_strtolower($text, 'UTF-8');
    foreach ($words as $word) {
        if (strpos($text, mb_strtolower($word, 'UTF-8')) === false) {
            return false;
        }
    }
    return true;
}

/**
 * Returns a list of all pages that contain all words in a co-content.
 * If $name === NULL the main content will be searched.
 *
 * @param string $name  A co-content name.
 * @param array  $words An array of words.
 * 
 * @return array
 *
 * @global array The content of the pages.
 * @global int   The number of pages.
 * @global array The configuration of the core.
 */
function Coco_searchContent($name, $words)
{
    global $c, $cl, $cf;

    $ta = array();
    for ($i = 0; $i < $cl; $i++) {
        if (!hide($i) || $cf['hidden']['pages_search'] == 'true') {
            $text = !isset($name) ? $c[$i] : Coco_get($name, $i);
            if (Coco_search($words, $text)) {
                $ta[] = $i;
            }
        }
    }
    return $ta;
}

/**
 * Returns the search results view.
 *
 * @return string (X)HTML.
 *
 * @global string The search string.
 * @global string The script name.
 * @global array  The headings of the pages.
 * @global array  The URLs of the pages.
 * @global array  The localization of the core.
 * @global array  The localization of the plugins.
 */
function Coco_searchResults()
{
    global $search, $sn, $h, $u, $tx, $plugin_cf;

    $o = '';
    $words = preg_split('/\s+/isu', stsl($search), null, PREG_SPLIT_NO_EMPTY);
    $ta = Coco_searchContent(null, $words);
    foreach (Coco_cocos() as $name) {
        $ta = array_merge($ta, Coco_searchContent($name, $words));
    }
    $ta = array_unique($ta);
    sort($ta);
    $o .= '<h1>' . $tx['search']['result'] . '</h1>' . PHP_EOL
        . '<p>"' . htmlspecialchars($search, ENT_COMPAT, 'UTF-8') . '" ';
    if (count($ta) == 0) {
        $o .= $tx['search']['notfound'];
    } else {
        $o .= $tx['search']['foundin'] . ' ' . count($ta) . ' ';
        if (count($ta) > 1) {
            $o .= $tx['search']['pgplural'];
        } else {
            $o .= $tx['search']['pgsingular'];
        }
        $o .= ':';
    }
    $o .= '</p>' . PHP_EOL;
    if (count($ta) > 0) {
        $o .= '<ul>' . PHP_EOL;
        $words = implode(',', $words);
        foreach ($ta as $i) {
            $o .= '<li>' . a($i, '&amp;search=' . urlencode($words))
                . $h[$i] . '</a></li>' . PHP_EOL;
        }
        $o .= '</ul>' . PHP_EOL;
    }
    return $o;
}

/*
 * Search.
 */
$title = $tx['title']['search'];
$o .= Coco_searchResults();

?>
