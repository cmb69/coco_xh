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

/**
 * @phpstan-import-type BackupName from Util
 */
class Repository
{
    /** @var string */
    private $dataFolder;

    /** @var string */
    private $contentFile;

    /** @var Pages */
    private $pages;

    /** @var IdGenerator */
    private $idGenerator;

    public function __construct(string $dataFolder, string $contentFile, Pages $pages, IdGenerator $idGenerator)
    {
        $this->dataFolder = $dataFolder;
        $this->contentFile = $contentFile;
        $this->pages = $pages;
        $this->idGenerator = $idGenerator;
    }

    public function dataFolder(): string
    {
        if (!is_dir($this->dataFolder)) {
            mkdir($this->dataFolder, 0777, true);
            chmod($this->dataFolder, 0777);
        }
        return $this->dataFolder;
    }

    public function filename(string $name, ?string $date = null): string
    {
        return $this->dataFolder() . ($date !== null ? "{$date}_" : "") . "$name.htm";
    }

    /** @return list<string> */
    public function findAllNames(): array
    {
        $predicate = function ($filename) {
            return Util::isCocoFilename($filename) && !Util::isBackup($filename);
        };
        $namer = function ($filename) {
            return basename($filename, '.htm');
        };
        return $this->doFindAllNames($predicate, $namer, function ($a, $b) {
            return $a <=> $b;
        });
    }

    /** @return list<BackupName> */
    public function findAllBackups(string $coconame): array
    {
        $predicate = function ($filename) use ($coconame) {
            return Util::isBackup($filename, $coconame);
        };
        return $this->doFindAllNames($predicate, [Util::class, "backupName"], function ($a, $b) {
            return $a[1] <=> $b[1];
        });
    }

    /**
     * @template T
     * @param callable(string):bool $predicate
     * @param callable(string):T $namer
     * @param callable(T,T):int $comparer
     * @return list<T>
     */
    private function doFindAllNames($predicate, $namer, $comparer): array
    {
        $result = [];
        if (($dir = opendir($this->dataFolder()))) {
            while (($filename = readdir($dir)) !== false) {
                if ($predicate($filename)) {
                    $result[] = $namer($filename);
                }
            }
            closedir($dir);
        }
        usort($result, $comparer);
        return array_values($result);
    }

    /** @return iterable<int,string> */
    public function findAll(string $name)
    {
        if (($text = $this->readContents($name)) === "") {
            return [];
        }
        for ($i = 0; $i < $this->pages->count(); $i++) {
            yield $this->cocoContent($text, $i);
        }
    }

    public function find(string $name, int $index): string
    {
        if (($text = $this->readContents($name)) === "") {
            return "";
        }
        return $this->cocoContent($text, $index);
    }

    private function cocoContent(string $text, int $index): string
    {
        $pd = $this->pages->data($index);
        if (empty($pd['coco_id'])) {
            return "";
        }
        return Util::cocoContent($text, $pd['coco_id']);
    }

    public function save(string $name, int $index, string $text): bool
    {
        $oldContent = $this->readContents($name);
        $content = "<html>\n<body>\n";
        for ($i = 0; $i < $this->pages->count(); $i++) {
            if (($id = $this->cocoId($i, $i === $index)) === null) {
                continue;
            }
            $content .= $this->headingLine($this->pages->level($i), $id, $this->pages->heading($i)) . "\n"
                . $this->content($i === $index, $id, $text, $oldContent);
        }
        $content .= "</body>\n</html>\n";
        $filename = $this->filename($name);
        if (is_dir($filename) || XH_writeFile($filename, $content) === false) {
            return false;
        }
        touch($this->contentFile);
        return true;
    }

    private function readContents(string $coconame): string
    {
        $filename = $this->filename($coconame);
        return is_file($filename) && is_readable($filename) ? (string) XH_readFile($filename) : "";
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
            return Util::cocoContent($oldContent, $id) . "\n";
        }
        $text = trim($text);
        if ($text !== "") {
            return $text . "\n";
        }
        return "";
    }

    public function backup(string $coconame, string $date): bool
    {
        return copy($this->filename($coconame), $this->filename($coconame, $date));
    }

    public function delete(string $coconame, ?string $date = null): bool
    {
        return unlink($this->filename($coconame, $date));
    }
}
