<?php

/*
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

namespace Coco\Value;

class Url
{
    /** @var string */
    private $base;

    /** @var string */
    private $path;

    /** @var string */
    private $page;

    /** @var array<string,string|array<string>> */
    private $params;

    public static function from(string $url): self
    {
        $that = new self();
        $parts = parse_url($url);
        assert(isset($parts["scheme"], $parts["host"], $parts["path"]));
        $that->base = $parts["scheme"] . "://" . $parts["host"];
        $that->path = (string) preg_replace('/index\.php$/', "", $parts["path"]);
        $match = preg_match('/^(?:([^=&]*)(?=&|$))?(.*)/', $parts["query"] ?? "", $matches);
        assert($match !== false);
        assert(isset($matches[1]));
        assert(isset($matches[2]));
        $that->page = $matches[1];
        $that->params = self::parseQuery($matches[2]);
        return $that;
    }

    /** @return array<string,string|array<string>> */
    private static function parseQuery(string $query): array
    {
        parse_str($query, $result);
        self::assertStringKeys($result);
        return $result;
    }

    /**
     * @param array<int|string,array<mixed>|string> $array
     * @phpstan-assert array<string,string|array<string>> $array
     */
    private static function assertStringKeys(array $array): void
    {
        foreach ($array as $key => $value) {
            assert(is_string($key));
        }
    }

    public function withPage(string $page): self
    {
        $that = clone $this;
        $that->page = $page;
        $that->params = [];
        return $that;
    }

    public function withParam(string $key, string $value = ""): self
    {
        $that = clone $this;
        $that->params[$key] = $value;
        return $that;
    }

    /** @return string|array<string>|null */
    public function param(string $key)
    {
        return $this->params[$key] ?? null;
    }

    public function relative(): string
    {
        $query = $this->queryString();
        if ($query === "") {
            return $this->path;
        }
        return $this->path . "?" . $query;
    }

    public function absolute(): string
    {
        $query = $this->queryString();
        if ($query === "") {
            return $this->base . $this->path;
        }
        return $this->base . $this->path . "?" . $query;
    }

    private function queryString(): string
    {
        $query = preg_replace('/=(?=&|$)/', "", http_build_query($this->params, "", "&"));
        if ($query === "") {
            return $this->page;
        }
        return $this->page . "&" . $query;
    }
}
