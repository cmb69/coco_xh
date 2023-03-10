<?php

/**
 * Copyright 2012-2023 Christoph M. Becker
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

namespace Coco\Infra;

use Coco\Logic\Util;

class CocoService
{
    /** @var string */
    private $dataDir;

    /** @var string */
    private $contentFile;

    /** @var Pages */
    private $pages;

    /** @var IdGenerator */
    private $idGenerator;

    /**
     * @param string $dataDir
     * @param string $contentFile
     */
    public function __construct($dataDir, $contentFile, Pages $pages, IdGenerator $idGenerator)
    {
        $this->dataDir = $dataDir;
        $this->contentFile = $contentFile;
        $this->pages = $pages;
        $this->idGenerator = $idGenerator;
    }

    /**
     * @return string
     */
    public function dataDir()
    {
        if (!is_dir($this->dataDir)) {
            mkdir($this->dataDir, 0777, true);
            chmod($this->dataDir, 0777);
        }
        return $this->dataDir;
    }

    /**
     * @param string $name
     * @return string
     */
    public function filename($name)
    {
        return $this->dataDir() . "/$name.htm";
    }

    /**
     * @return string[]
     */
    public function findAllNames()
    {
        $cocos = [];
        if (($dir = opendir($this->dataDir()))) {
            while (($filename = readdir($dir)) !== false) {
                if (Util::isCocoFilename($filename) && !Util::isBackup($filename)) {
                    $cocos[] = basename($filename, '.htm');
                }
            }
            closedir($dir);
        }
        return $cocos;
    }

    /**
     * @param string $name
     * @return iterable<int,string>
     */
    public function findAll($name)
    {
        $fn = $this->filename($name);
        if (!is_readable($fn) || ($text = XH_readFile($fn)) === false) {
            return [];
        }
        for ($i = 0; $i < $this->pages->count(); $i++) {
            $pd = $this->pages->data($i);
            if (empty($pd['coco_id'])) {
                yield "";
            } else {
                yield Util::cocoContent($text, $pd['coco_id']);
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
        $pd = $this->pages->data($i);
        if (empty($pd['coco_id'])) {
            return "";
        }
        return Util::cocoContent($text, $pd['coco_id']);
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
        for ($j = 0; $j < $this->pages->count(); $j++) {
            $pd = $this->pages->data($j);
            if (empty($pd['coco_id'])) {
                if ($j !== $i) {
                    continue;
                }
                $pd['coco_id'] = $this->idGenerator->newId();
                $this->pages->updateData($j, $pd);
            }
            $cnt .= '<h' . $this->pages->level($j) . ' id="' . $pd['coco_id'] . '">' . $this->pages->heading($j)
                . '</h' . $this->pages->level($j) . '>' . PHP_EOL;
            if ($j == $i) {
                $text = trim($text);
                if (!empty($text)) {
                    $cnt .= $text . PHP_EOL;
                }
            } else {
                $cnt .= Util::cocoContent($old, $pd["coco_id"]);
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
     * @return list<string>
     */
    public function delete($name)
    {
        $result = [];
        if (($dir = opendir($this->dataDir()))) {
            while (($filename = readdir($dir)) !== false) {
                if (Util::isBackup($filename, $name)) {
                    $filename = $this->dataDir() . "/" . $filename;
                    if (!(is_file($filename) && unlink($filename))) {
                        $result[] = $filename;
                    }
                }
            }
            closedir($dir);
        }
        $filename = $this->filename($name);
        if (!(is_file($filename) && unlink($filename))) {
            $result[] = $filename;
        }
        return $result;
    }
}
