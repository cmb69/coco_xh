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
    private $contentFile;

    /** @var Pages */
    private $pages;

    /** @var PageData */
    private $pageData;

    /**
     * @param string $contentFile
     */
    public function __construct($contentFile, Pages $pages, PageData $pageData)
    {
        $this->contentFile = $contentFile;
        $this->pages = $pages;
        $this->pageData = $pageData;
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
            $fn = Plugin::dataFolder() . $name . '.htm';
            if (!is_readable($fn) || ($text = XH_readFile($fn)) === false) {
                e('cntopen', 'file', $fn);
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
     * @return void
     */
    public function save($name, $i, $text)
    {
        global $cf;

        $fn = Plugin::dataFolder() . $name . '.htm';
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
        if (XH_writeFile($fn, $cnt) !== false) {
            touch($this->contentFile);
        } else {
            e('cntwriteto', 'file', $fn);
        }
    }
}
