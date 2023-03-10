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

namespace Coco;

use Coco\Infra\Pages;
use Coco\Infra\Repository;
use Coco\Infra\Request;
use Coco\Infra\View;
use Coco\Infra\XhStuff;
use Coco\Logic\Util;
use Coco\Value\Response;

class Search
{
    /** @var Repository */
    private $repository;

    /** @var Pages */
    private $pages;

    /** @var XhStuff */
    private $xhStuff;

    /** @var View */
    private $view;

    public function __construct(Repository $repository, Pages $pages, XhStuff $xhStuff, View $view)
    {
        $this->repository = $repository;
        $this->pages = $pages;
        $this->xhStuff = $xhStuff;
        $this->view = $view;
    }

    public function __invoke(Request $request): Response
    {
        $words = Util::parseSearchTerm($request->search());
        $indexes = $this->searchContent(null, $words);
        foreach ($this->repository->findAllNames() as $name) {
            $indexes = array_merge($indexes, $this->searchContent($name, $words));
        }
        $indexes = array_unique($indexes);
        sort($indexes);
        return Response::create($this->renderSearchResults($indexes, $request->sn(), $words))
            ->withTitle($this->view->text("search_title"));
    }

    /**
     * @param list<string> $words
     * @return list<int>
     */
    private function searchContent(?string $name, array $words): array
    {
        $contents = $name === null ? $this->pages->contents() : $this->repository->findAll($name);
        $indexes = [];
        foreach ($contents as $index => $content) {
            if (!$this->pages->isHidden($index) && $this->findAllIn($words, $content)) {
                $indexes[] = $index;
            }
        }
        return $indexes;
    }

    /** @param list<string> $words */
    private function findAllIn(array $words, string $text): bool
    {
        $text = strip_tags($this->xhStuff->evaluateScripting($text));
        $text = html_entity_decode($text, ENT_QUOTES, "UTF-8");
        return Util::textContainsAllWords($text, $words);
    }

    /**
     * @param list<int> $pageIndexes
     * @param list<string> $searchWords
     */
    private function renderSearchResults(array $pageIndexes, string $sn, array $searchWords): string
    {
        return $this->view->render("search_results", [
            "search_term" => implode(" ", $searchWords),
            "pages" => $this->pageRecords($pageIndexes, $sn, implode(",", $searchWords)),
        ]);
    }

    /**
     * @param list<int> $pageIndexes
     * @return list<array{heading:string,url:string}>
     */
    private function pageRecords(array $pageIndexes, string $sn, string $searchWords): array
    {
        $records = [];
        foreach ($pageIndexes as $pageIndex) {
            $records[] = [
                "heading" => $this->pages->heading($pageIndex),
                "url" => $sn . "?" . $this->pages->url($pageIndex) . "&search=" . urlencode($searchWords),
            ];
        }
        return $records;
    }
}
