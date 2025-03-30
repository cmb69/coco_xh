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
use Coco\Infra\XhStuff;
use Coco\Logic\Searcher;
use Coco\Value\Url;
use Plib\Response;
use Plib\View;

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
        $words = Searcher::parseSearchTerm($request->search());
        $indexes = Searcher::search($words, $this->contents());
        return Response::create($this->renderSearchResults($indexes, $request->url(), implode(" ", $words)))
            ->withTitle($this->view->text("search_title"));
    }

    /** @return iterable<int,string> */
    private function contents(): iterable
    {
        foreach ($this->pages->contents() as $index => $content) {
            if (!$this->pages->isHidden($index)) {
                yield $index => $this->xhStuff->evaluateScripting($content);
            }
        }
        foreach ($this->repository->findAllNames() as $name) {
            foreach ($this->repository->findAll($name) as $index => $content) {
                if (!$this->pages->isHidden($index)) {
                    yield $index => $this->xhStuff->evaluateScripting($content);
                }
            }
        }
    }

    /** @param list<int> $pageIndexes */
    private function renderSearchResults(array $pageIndexes, Url $url, string $searchTerm): string
    {
        return $this->view->render("search_results", [
            "search_term" => $searchTerm,
            "pages" => $this->pageRecords($pageIndexes, $url, $searchTerm),
        ]);
    }

    /**
     * @param list<int> $pageIndexes
     * @return list<array{heading:string,url:string}>
     */
    private function pageRecords(array $pageIndexes, Url $url, string $searchTerm): array
    {
        return array_map(function (int $pageIndex) use ($url, $searchTerm) {
            return [
                "heading" => $this->pages->heading($pageIndex),
                "url" => $url->withPage($this->pages->url($pageIndex))->withParam("search", $searchTerm)->relative(),
            ];
        }, $pageIndexes);
    }
}
