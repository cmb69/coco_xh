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

    public function save(string $name, int $index, string $text): bool
    {
        $filename = $this->filename($name);
        $oldContent = is_readable($filename) ? (string) XH_readFile($filename) : '';
        $content = "<html>\n<body>\n";
        for ($i = 0; $i < $this->pages->count(); $i++) {
            if (($id = $this->cocoId($i, $i === $index)) === null) {
                continue;
            }
            $content .= $this->headingLine($this->pages->level($i), $id, $this->pages->heading($i)) . "\n"
                . $this->content($i === $index, $id, $text, $oldContent);
        }
        $content .= "</body>\n</html>\n";
        if (XH_writeFile($filename, $content) === false) {
            return false;
        }
        touch($this->contentFile);
        return true;
    }

    private function cocoId(int $index, bool $current): ?string
    {
        $pd = $this->pages->data($index);
        if (empty($pd["coco_id"])) {
            if (!$current) {
                return null;
            }
            $pd["coco_id"] = $this->idGenerator->newId();
            $this->pages->updateData($index, $pd);
        }
        return $pd["coco_id"];
    }

    private function headingLine(int $level, string $id, string $heading): string
    {
        return "<h$level id=\"$id\">$heading</h$level>";
    }

    private function content(bool $current, string $id, string $text, string $oldContent): string
    {
        if (!$current) {
            return Util::cocoContent($oldContent, $id);
        }
        $text = trim($text);
        if ($text !== "") {
            return $text . "\n";
        }
        return "";
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
