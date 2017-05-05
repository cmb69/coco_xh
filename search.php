<?php

/**
 * Search functionality of Coco_XH.
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
 * Prevent direct access.
 */
if (!defined('CMSIMPLE_XH_VERSION')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

/**
 * The Utf8_XH utility plugin.
 */
require_once $pth['folder']['plugins'] . 'utf8/utf8.php';

/**
 * Decodes all HTML entities in a text.
 *
 * As html_entity_decode() doesn't work for UTF-8 strings before PHP 5.0.0,
 * we provide a simplified fallback.
 *
 * @param string $text A text.
 *
 * @return string
 */
function Coco_decodeEntities($text)
{
    if (version_compare(phpversion(), '5.0.0', '>=')) {
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
    } else {
        $replacePairs = array(
            '&amp;' => '&',
            '&quot;' => '"',
            '&apos;' => '\'',
            '&lt;' => '<',
            '&gt;' => '>'
        );
        $text = strtr($text, $replacePairs);
    }
    return $text;
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
    $text = strip_tags(evaluate_scripting($text));
    $text = Coco_decodeEntities($text, ENT_QUOTES, 'UTF-8');
    $text = utf8_strtolower($text, 'UTF-8');
    foreach ($words as $word) {
        if (strpos($text, utf8_strtolower($word, 'UTF-8')) === false) {
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
 * @global array  The configuration of the plugins.
 * @global array  The localization of the plugins.
 */
function Coco_searchResults()
{
    global $search, $sn, $h, $u, $plugin_cf, $plugin_tx;

    $ptx = $plugin_tx['coco'];
    $o = '';
    $words = preg_split('/\s+/isu', $search, null, PREG_SPLIT_NO_EMPTY);
    $ta = Coco_searchContent(null, $words);
    foreach (Coco_cocos() as $name) {
        $ta = array_merge($ta, Coco_searchContent($name, $words));
    }
    $ta = array_unique($ta);
    sort($ta);
    $o .= '<h1>' . $ptx['search_result'] . '</h1>' . PHP_EOL
        . '<p>"' . htmlspecialchars($search, ENT_COMPAT, 'UTF-8') . '" ';
    if (count($ta) == 0) {
        $o .= $ptx['search_notfound'];
    } else {
        $o .= $ptx['search_foundin'] . ' ' . count($ta) . ' ';
        if (count($ta) > 1) {
            $o .= $ptx['search_pgplural'];
        } else {
            $o .= $ptx['search_pgsingular'];
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
