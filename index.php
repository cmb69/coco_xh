<?php

/**
 * Front-End of Coco_XH.
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
 * The plugin version.
 */
define('COCO_VERSION', '@COCO_VERSION@');

/**
 * Reads a file and returns its contents; <var>false</var> on failure.
 * During reading, the file is locked for shared access.
 *
 * @param string $filename A file path.
 *
 * @return string
 */
function Coco_readFile($filename)
{
    $contents = false;
    $stream = fopen($filename, 'rb');
    if ($stream) {
        if (flock($stream, LOCK_SH)) {
            $contents = stream_get_contents($stream);
            flock($stream, LOCK_UN);
        }
        fclose($stream);
    }
    return $contents;
}

/**
 * Writes <var>$contents</var> to the file <var>$filename</var>.
 * During writing the file is locked exclusively.
 *
 * @param string $filename The filename.
 * @param string $contents The content to write.
 *
 * @return int The number of bytes written, or false on failure.
 */
function Coco_writeFile($filename, $contents)
{
    $res = false;
    $stream = fopen($filename, 'cb');
    if ($stream) {
        if (flock($stream, LOCK_EX)) {
            ftruncate($stream, 0);
            $res = fwrite($stream, $contents);
            fflush($stream);
            flock($stream, LOCK_UN);
        }
        fclose($stream);
    }
    return $res;
}

/**
 * Returns the path of the data folder.
 *
 * @return string
 *
 * @global array  The paths of system files and folders.
 * @global string The current language.
 * @global array  The configuration of the core.
 * @global array  The configuration of the plugins.
 */
function Coco_dataFolder()
{
    global $pth, $sl, $cf, $plugin_cf;

    $pcf = $plugin_cf['coco'];

    if ($pcf['folder_data'] == '') {
        $fn = $pth['folder']['plugins'] . 'coco/data/';
    } else {
        $fn = $pth['folder']['base'] . $pcf['folder_data'];
    }
    if (substr($fn, -1) != '/') {
        $fn .= '/';
    }
    if (file_exists($fn)) {
        if (!is_dir($fn)) {
            e('cntopen', 'folder', $fn);
        }
    } else {
        if (!mkdir($fn, 0777, true)) {
            e('cntwriteto', 'folder', $fn);
        }
    }
    if ($sl != $cf['language']['default']) {
        $fn .= $sl . '/';
        if (file_exists($fn)) {
            if (!is_dir($fn)) {
                e('cntopen', 'folder', $fn);
            }
        } else {
            if (!mkdir($fn, 0777, true)) {
                e('cntwriteto', 'folder', $fn);
            }
        }
    }
    return $fn;
}

/**
 * Returns all available co-contents.
 *
 * @return array
 */
function Coco_cocos()
{
    $cocos = glob(Coco_dataFolder() . '*.htm');
    $func = function ($fn) {
        return basename($fn, '.htm');
    };                        
    $cocos = array_map($func, $cocos);
    $func = function ($fn) {
        return !preg_match('/^\d{8}_\d{6}_/', $fn);
    };
    $cocos = array_filter($cocos, $func);
    return $cocos;
}

/**
 * Returns the co-content of a page.
 *
 * @param string $name A co-content name.
 * @param int    $i    A page index.
 *
 * @return string
 *
 * @global array  The configuration of the core.
 * @global object The page data router.
 */
function Coco_get($name, $i)
{
    global $cf, $pd_router;
    static $curname = null;
    static $text = null;

    $pd = $pd_router->find_page($i);
    if (empty($pd['coco_id'])) {
        return '';
    }
    if ($name != $curname) {
        $curname = $name;
        $fn = Coco_dataFolder() . $name . '.htm';
        if (!is_readable($fn) || ($text = Coco_readFile($fn)) === false) {
            e('cntopen', 'file', $fn);
            return false;
        }
    }
    $ml = $cf['menu']['levels'];
    preg_match(
        '/<h[1-' . $ml . '].*?id="' . $pd['coco_id'] . '".*?>.*?'
        . '<\/h[1-' . $ml . ']>(.*?)<(?:h[1-' . $ml . ']|\/body)/isu',
        $text, $matches
    );
    return !empty($matches[1]) ? trim($matches[1]) : '';
}

/**
 * Saves a text as co-content of a page.
 *
 * @param string $name A co-content name.
 * @param int    $i    A page index.
 * @param string $text A new co-content.
 *
 * @return void
 *
 * @global array  The paths of system files and folders.
 * @global int    The number of pages.
 * @global array  The levels of the pages.
 * @global array  The headings of the pages.
 * @global array  The configuration of the core.
 * @global object The page data router.
 */
function Coco_set($name, $i, $text)
{
    global $pth, $cl, $l, $h, $cf, $pd_router;

    $fn = Coco_dataFolder() . $name . '.htm';
    $old = is_readable($fn) ? Coco_readFile($fn) : '';
    $ml = $cf['menu']['levels'];
    $cnt = '<html>' . PHP_EOL . '<body>' . PHP_EOL;
    for ($j = 0; $j < $cl; $j++) {
        $pd = $pd_router->find_page($j);
        if (empty($pd['coco_id'])) {
            $pd['coco_id'] = uniqid();
            $pd_router->update($j, $pd);
        }
        $cnt .= '<h' . $l[$j] . ' id="' . $pd['coco_id'] . '">' . $h[$j]
            . '</h' . $l[$j] . '>' . PHP_EOL;
        if ($j == $i) {
            $text = preg_replace(
                '/<h' . $ml . '.*?>.*?<\/h' . $ml . '>/isu', '', $text
            );
            $text = trim($text);
            $text = preg_replace(
                '/(<\/?h)[1-' . $ml . ']/is', '${1}' . ($ml + 1), $text
            );
            if (!empty($text)) {
                $cnt .= $text . PHP_EOL;
            }
        } else {
            preg_match(
                '/<h[1-' . $ml . '].*?id="' . $pd['coco_id'] . '".*?>.*?'
                . '<\/h[1-' . $ml . ']>(.*?)<(?:h[1-' . $ml . ']|\/body)/isu',
                $old, $matches
            );
            $cnt .= isset($matches[1]) && ($match = trim($matches[1])) != ''
                ? $match . PHP_EOL
                : '';
        }
    }
    $cnt .= '</body>' . PHP_EOL . '</html>' . PHP_EOL;
    if (Coco_writeFile($fn, $cnt) !== false) {
        touch($pth['file']['content']);
    } else {
        e('cntwriteto', 'file', $fn);
    }
}

/**
 * Creates new backups of all co-contents and deletes superfluous ones.
 * Returns the success messages. Errors are signalled via e().
 *
 * @return string
 *
 * @global array  The configuration of the core.
 * @global array  The localization of the core.
 * @global string The backup date of the core (only available before XH 1.6).
 */
function Coco_backup()
{
    global $cf, $tx, $backupDate;

    $dir = Coco_dataFolder();
    if (!isset($backupDate)) {
        $backupDate = date("Ymd_His");
    }
    $o = '';
    foreach (Coco_cocos() as $coco) {
        $fn = $dir . $backupDate . '_' . $coco . '.htm';
        if (copy($dir . $coco . '.htm', $fn)) {
            $o .= XH_message(
                'info', 
                ucfirst($tx['filetype']['backup']) . ' ' . $fn . ' '
                . $tx['result']['created']
            ) . PHP_EOL;
            $bus = glob($dir . '????????_??????_' . $coco . '.htm');
            for ($i = 0; $i < count($bus) - $cf['backup']['numberoffiles']; $i++) {
                if (unlink($bus[$i])) {
                    $o .= XH_message(
                        'info',
                        ucfirst($tx['filetype']['backup']) . ' ' . $bus[$i]
                        . ' ' . $tx['result']['deleted']
                    ) . PHP_EOL;
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
 * @param string $name   A co-content name.
 * @param string $config An editor configuration.
 * @param string $height An editor height as CSS length.
 *
 * @return string (X)HTML.
 *
 * @global bool   Whether we're in admin mode.
 * @global bool   Whether we're in edit mode.
 * @global int    The current page index.
 * @global int    The number of pages.
 * @global string The (X)HTML fragment containing error messages.
 * @global array  The localization of the core.
 * @global array  The localiaztion of the plugins.
 *
 * @access public
 */
function coco($name, $config = false, $height = '100%')
{
    global $adm, $edit, $s, $cl, $e, $tx, $plugin_tx, $_XH_csrfProtection;

    if (!preg_match('/^[a-z_0-9]+$/su', $name)) {
        return '<div class="cmsimplecore_warning">'
            . $plugin_tx['coco']['error_invalid_name'] . '</div>' . PHP_EOL;
    }
    if ($s < 0 || $s >= $cl) {
        return '';
    }
    $o = '';
    if ($adm && $edit) {
        if (isset($_POST['coco_text_' . $name])) {
            $_XH_csrfProtection->check();
            Coco_set($name, $s, $_POST['coco_text_' . $name]);
        }
        $id = 'coco_text_' . $name;
        $style = 'width:100%; height:' . $height;
        $er = function_exists('editor_replace')
            ? editor_replace($id, $config)
            : false;
        $o .= '<form action="" method="POST">' . PHP_EOL
            . '<textarea id="' . $id . '" name="coco_text_' . $name . '" style="'
            . $style.'">'
            . XH_hsc(Coco_get($name, $s))
            . '</textarea>' . PHP_EOL;
        if (!$er) {
            $o .= tag(
                'input type="submit" class="submit" value="'
                . ucfirst($tx['action']['save']) . '"'
            );
        }
        $o .= $_XH_csrfProtection->tokenInput() . '</form>' . PHP_EOL;

        if ($er) {
            $o .= '<script type="text/javascript">/* <![CDATA[ */' . PHP_EOL
                . $er . PHP_EOL . '/* ]]> */</script>' . PHP_EOL;
        }
    } else {
        $text = evaluate_scripting(Coco_get($name, $s));
        if (isset($_GET['search'])) {
            $class = 'xh_find';
            $search = urldecode($_GET['search']);
            $search = XH_hsc($search);
            $words = explode(',', $search);
            $func = function($w) {
                return "/" . preg_quote($w, "/") . "(?!([^<]+)?>)/isU";
            };
            $words = array_map($func, $words);
            $text = preg_replace(
                $words, '<span class="' . $class . '">\\0</span>', $text
            );
        }
        $o .= $text;
    }
    return $o;
}

/*
 * Register the coco id in the page data.
 */
$pd_router->add_interest('coco_id');

/*
 * Create and delete backups.
 */
if ($f == 'xh_loggedout') {
    $o .= Coco_backup();
}

?>
