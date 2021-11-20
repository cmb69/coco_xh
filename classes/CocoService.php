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

    /** @var IdGenerator */
    private $idGenerator;

    /**
     * @param string $dataDir
     * @param string $contentFile
     */
    public function __construct($dataDir, $contentFile, Pages $pages, PageData $pageData, IdGenerator $idGenerator)
    {
        if (!is_dir($dataDir)) {
            mkdir($dataDir, 0777, true);
            chmod($dataDir, 0777);
        }
        $this->dataDir = $dataDir;
        $this->contentFile = $contentFile;
        $this->pages = $pages;
        $this->pageData = $pageData;
        $this->idGenerator = $idGenerator;
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
    public function findAllNames()
    {
        $cocos = [];
        if ($dir = opendir($this->dataDir)) {
            while (($filename = readdir($dir)) !== false) {
                if (preg_match('/\.htm$/', $filename) && !preg_match('/^\d{8}_\d{6}_/', $filename)) {
                    $cocos[] = basename($filename, '.htm');
                }
            }
        }
        return $cocos;
    }

    /**
     * @param string $name
     * @return iterable<string>
     */
    public function findAll($name)
    {
        $fn = $this->filename($name);
        if (!is_readable($fn) || ($text = XH_readFile($fn)) === false) {
            return [];
        }
        for ($i = 0; $i < $this->pages->getCount(); $i++) {
            $pd = $this->pageData->find_page($i);
            if (empty($pd['coco_id'])) {
                yield "";
            } else {
                yield $this->doFind($text, $pd['coco_id']);
            }
        }
    }

    /**
     * @param string $name
     * @param int $i
     * @return string
     */
    public function find($name, $i)
    {
        $fn = $this->filename($name);
        if (!is_readable($fn) || ($text = XH_readFile($fn)) === false) {
            return "";
        }
        $pd = $this->pageData->find_page($i);
        if (empty($pd['coco_id'])) {
            return "";
        }
        return $this->doFind($text, $pd['coco_id']);
    }

    /**
     * @param string $name
     * @param int $i
     * @param string $text
     * @return bool
     */
    public function save($name, $i, $text)
    {
        $fn = $this->filename($name);
        $old = is_readable($fn) ? (string) XH_readFile($fn) : '';
        $cnt = '<html>' . PHP_EOL . '<body>' . PHP_EOL;
        for ($j = 0; $j < $this->pages->getCount(); $j++) {
            $pd = $this->pageData->find_page($j);
            if (empty($pd['coco_id'])) {
                if ($j !== $i) {
                    continue;
                }
                $pd['coco_id'] = $this->idGenerator->newId();
                $this->pageData->update($j, $pd);
            }
            $cnt .= '<h' . $this->pages->level($j) . ' id="' . $pd['coco_id'] . '">' . $this->pages->heading($j)
                . '</h' . $this->pages->level($j) . '>' . PHP_EOL;
            if ($j == $i) {
                $text = trim($text);
                if (!empty($text)) {
                    $cnt .= $text . PHP_EOL;
                }
            } else {
                $cnt .= $this->doFind($old, $pd["coco_id"]);
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
     * @param string $content
     * @param string $id
     * @return string
     */
    private function doFind($content, $id)
    {
        $pattern = sprintf(
            '/<h[1-9][^>]+id="%s"[^>]*>[^<]*<\/h[1-9]>(.*?)<(?:h[1-9][^>]+?id=|\/body)/isu',
            $id
        );
        preg_match($pattern, $content, $matches);
        return !empty($matches[1]) ? trim($matches[1]) : '';
    }

    /**
     * @param string $name
     * @return array<string,bool>
     */
    public function delete($name)
    {
        $result = [];
        if (($dir = opendir($this->dataDir))) {
            $pattern = sprintf('/^\d{8}_\d{6}_%s\.htm$/', $name);
            if (($filename = readdir($dir)) !== false) {
                if (preg_match($pattern, $filename)) {
                    $result[$filename] = unlink($filename);
                }
            }
        }
        $filename = $this->filename($name);
        $result[$filename] = unlink($filename);
        return $result;
    }
}
