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

namespace Coco\Logic;

class Searcher
{
    /** @return list<string> */
    public static function parseSearchTerm(string $searchTerm): array
    {
        return preg_split('/\s+/iu', $searchTerm, 0, PREG_SPLIT_NO_EMPTY) ?: [];
    }

    /**
     * @param list<string> $words
     * @param iterable<int,string> $contents
     * @return list<int>
     */
    public static function search(array $words, iterable $contents): array
    {
        $indexes = [];
        foreach ($contents as $index => $content) {
            if (self::findAllIn($words, $content)) {
                $indexes[] = $index;
            }
        }
        $indexes = array_unique($indexes);
        sort($indexes);
        return $indexes;
    }

    /** @param list<string> $words */
    private static function findAllIn(array $words, string $text): bool
    {
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES, "UTF-8");
        return self::textContainsAllWords($text, $words);
    }

    /** @param list<string> $words */
    private static function textContainsAllWords(string $text, array $words): bool
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
