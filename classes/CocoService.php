<?php

/**
 * Copyright 2012-2021 Christoph M. Becker
 *
 * This file is part of Coco_XH.
 *
 * Coco_XH is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Coco_XH is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Coco_XH.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Coco;

use XH\PageDataRouter as PageData;
use XH\Pages;

final class CocoService
{
    /** @var string */
    private $dataDir;

    /** @var string */
    private $contentFile;

    /** @var Pages */
    private $pages;

    /** @var PageData */
    private $pageData;

    /**
     * @param string $dataDir
     * @param string $contentFile
     */
    public function __construct($dataDir, $contentFile, Pages $pages, PageData $pageData)
    {
        if (!is_dir($dataDir)) {
            mkdir($dataDir, 0777, true);
            chmod($dataDir, 0777);
        }
        $this->dataDir = $dataDir;
        $this->contentFile = $contentFile;
        $this->pages = $pages;
        $this->pageData = $pageData;
    }

    /**
     * @return string
     */
    public function dataDir()
    {
        return $this->dataDir;
    }

    /**
     * @param string $name
     * @return string
     */
    public function filename($name)
    {
        return "$this->dataDir/$name.htm";
    }

    /**
     * @return string[]
     */
    public function findAll()
    {
        $cocos = glob("$this->dataDir/*.htm") ?: [];
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
     * @param string $name
     * @param int $i
     * @return string|false
     */
    public function find($name, $i)
    {
        global $cf;
        static $curname = null;
        static $text = null;

        $pd = $this->pageData->find_page($i);
        if (empty($pd['coco_id'])) {
            return '';
        }
        if ($name != $curname) {
            $curname = $name;
            $fn = $this->filename($name);
            if (!is_readable($fn) || ($text = XH_readFile($fn)) === false) {
                return false;
            }
        }
        $ml = $cf['menu']['levels'];
        preg_match(
            '/<h[1-' . $ml . '].*?id="' . $pd['coco_id'] . '".*?>.*?'
            . '<\/h[1-' . $ml . ']>(.*?)<(?:h[1-' . $ml . ']|\/body)/isu',
            $text,
            $matches
        );
        return !empty($matches[1]) ? trim($matches[1]) : '';
    }

    /**
     * @param string $name
     * @param int $i
     * @param string $text
     * @return bool
     */
    public function save($name, $i, $text)
    {
        global $cf;

        $fn = $this->filename($name);
        $old = is_readable($fn) ? (string) XH_readFile($fn) : '';
        $ml = $cf['menu']['levels'];
        $cnt = '<html>' . PHP_EOL . '<body>' . PHP_EOL;
        for ($j = 0; $j < $this->pages->getCount(); $j++) {
            $pd = $this->pageData->find_page($j);
            if (empty($pd['coco_id'])) {
                $pd['coco_id'] = uniqid();
                $this->pageData->update($j, $pd);
            }
            $cnt .= '<h' . $this->pages->level($j) . ' id="' . $pd['coco_id'] . '">' . $this->pages->heading($j)
                . '</h' . $this->pages->level($j) . '>' . PHP_EOL;
            if ($j == $i) {
                $text = (string) preg_replace('/<h' . $ml . '.*?>.*?<\/h' . $ml . '>/isu', '', (string) $text);
                $text = trim($text);
                $text = preg_replace('/(<\/?h)[1-' . $ml . ']/is', '${1}' . ($ml + 1), $text);
                if (!empty($text)) {
                    $cnt .= $text . PHP_EOL;
                }
            } else {
                preg_match(
                    '/<h[1-' . $ml . '].*?id="' . $pd['coco_id'] . '".*?>.*?'
                    . '<\/h[1-' . $ml . ']>(.*?)<(?:h[1-' . $ml . ']|\/body)/isu',
                    $old,
                    $matches
                );
                $cnt .= isset($matches[1]) && ($match = trim($matches[1])) != ''
                    ? $match . PHP_EOL
                    : '';
            }
        }
        $cnt .= '</body>' . PHP_EOL . '</html>' . PHP_EOL;
        if (XH_writeFile($fn, $cnt) === false) {
            return false;
        }
        touch($this->contentFile);
        return true;
    }

    /**
     * @param string $name
     * @return array<string,bool>
     */
    public function delete($name)
    {
        $result = [];
        $fns = glob("$this->dataDir/????????_??????_$name.htm") ?: [];
        foreach ($fns as $fn) {
            $result[$fn] = unlink($fn);
        }
        $fn = "$this->dataDir/$name.htm";
        $result[$fn] = unlink($fn);
        return $result;
    }
}
