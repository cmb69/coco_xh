<?php

/**
 * Copyright (c) Christoph M. Becker
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
use Plib\Random;

class Repository
{
    /** @var string */
    private $dataFolder;

    /** @var string */
    private $contentFile;

    /** @var Pages */
    private $pages;

    /** @var Random */
    private $random;

    public function __construct(string $dataFolder, string $contentFile, Pages $pages, Random $random)
    {
        $this->dataFolder = $dataFolder;
        $this->contentFile = $contentFile;
        $this->pages = $pages;
        $this->random = $random;
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

    /** @return list<array{string,string}> */
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

    /**
     * @return void
     * @throws RepositoryException
     */
    public function save(string $name, int $index, string $text)
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
        if (is_dir($filename) || !$this->writeFile($filename, $content)) {
            throw new RepositoryException("can't save");
        }
        touch($this->contentFile);
    }

    private static function writeFile(string $filename, string $content): bool
    {
        $res = false;
        if (($stream = fopen($filename, "cb"))) {
            if (flock($stream, LOCK_EX)) {
                ftruncate($stream, 0);
                $res = fwrite($stream, $content) !== false;
                flock($stream, LOCK_UN);
            }
            fclose($stream);
        }
        return $res;
    }

    private function readContents(string $coconame): string
    {
        $filename = $this->filename($coconame);
        return is_file($filename) && is_readable($filename) ? $this->readFile($filename) : "";
    }

    private static function readFile(string $filename): string
    {
        $res = "";
        if (($stream = fopen($filename, "rb"))) {
            if (flock($stream, LOCK_SH)) {
                $res = (string) stream_get_contents($stream);
                flock($stream, LOCK_UN);
            }
            fclose($stream);
        }
        return $res;
    }

    private function cocoId(int $index, bool $current): ?string
    {
        $pd = $this->pages->data($index);
        if (empty($pd["coco_id"])) {
            if (!$current) {
                return null;
            }
            $pd["coco_id"] = $this->newId();
            $this->pages->updateData($index, $pd);
        }
        return $pd["coco_id"];
    }

    private function newId(): string
    {
        $rand = $this->random->bytes(16);
        $rand[6] = chr(ord($rand[6]) & 0x0f | 0x40);
        $rand[8] = chr(ord($rand[8]) & 0x3f | 0x80);
        $uuid = strtoupper(bin2hex($rand));
        return substr($uuid, 0, 8) . "-" . substr($uuid, 8, 4) . "-"
            . substr($uuid, 12, 4) . "-" . substr($uuid, 16, 4) . "-"
            . substr($uuid, 20, 12);
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

    /**
     * @return void
     * @throws RepositoryException
     */
    public function backup(string $coconame, string $date)
    {
        if (!copy($this->filename($coconame), $this->filename($coconame, $date))) {
            throw new RepositoryException("can't backup");
        }
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function delete(string $coconame, ?string $date = null)
    {
        if (!unlink($this->filename($coconame, $date))) {
            throw new RepositoryException("can't delete");
        }
    }
}
