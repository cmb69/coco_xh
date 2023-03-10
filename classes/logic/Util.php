<?php

/**
 * Copyright 2023 Christoph M. Becker
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

namespace Coco\Logic;

class Util
{
    public static function isValidCocoName(string $name): bool
    {
        return (bool) preg_match('/^[a-z_0-9]+$/u', $name);
    }

    /** @return list<string> */
    public static function parseSearchTerm(string $searchTerm): array
    {
        return preg_split('/\s+/iu', $searchTerm, 0, PREG_SPLIT_NO_EMPTY) ?: [];
    }

    public static function isCocoFilename(string $filename): bool
    {
        return (bool) preg_match('/^[a-z_0-9]+\.htm$/u', $filename);
    }

    public static function isBackup(string $filename, ?string $coconame = null): bool
    {
        assert($coconame === null || self::isValidCocoName($coconame));
        $name = $coconame ? preg_quote($coconame, "/") : "[a-z_0-9]+";
        return (bool) preg_match(sprintf('/^\d{8}_\d{6}_%s\.htm$/u', $name), $filename);
    }

    public static function backupPrefix(int $timestamp): string
    {
        return date("Ymd_His", $timestamp);
    }

    public static function cocoContent(string $content, string $id): string
    {
        $pattern = sprintf(
            '/<h[1-9][^>]+id="%s"[^>]*>[^<]*<\/h[1-9]>(.*?)<(?:h[1-9]|\/body)/isu',
            preg_quote($id, "/")
        );
        if (!preg_match($pattern, $content, $matches)) {
            return "";
        }
        return trim($matches[1]);
    }

    /** @param list<string> $words */
    public static function textContainsAllWords(string $text, array $words): bool
    {
        $text = utf8_strtolower($text);
        foreach ($words as $word) {
            if (strpos($text, utf8_strtolower($word)) === false) {
                return false;
            }
        }
        return true;
    }
}
