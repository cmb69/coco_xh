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

use Coco\Infra\CocoService;
use Coco\Infra\Pages;
use Coco\Infra\Request;
use Coco\Infra\Response;
use Coco\Infra\View;
use Coco\Infra\XhStuff;

class Search
{
    /** @var CocoService */
    private $cocoService;

    /** @var Pages */
    private $pages;

    /** @var XhStuff */
    private $xhStuff;

    /** @var View */
    private $view;

    public function __construct(CocoService $cocoService, Pages $pages, XhStuff $xhStuff, View $view)
    {
        $this->cocoService = $cocoService;
        $this->pages = $pages;
        $this->xhStuff = $xhStuff;
        $this->view = $view;
    }

    public function __invoke(Request $request): Response
    {
        $words = preg_split('/\s+/isu', $request->search(), 0, PREG_SPLIT_NO_EMPTY) ?: [];
        $ta = $this->searchContent(null, $words);
        foreach ($this->cocoService->findAllNames() as $name) {
            $ta = array_merge($ta, $this->searchContent($name, $words));
        }
        $ta = array_unique($ta);
        sort($ta);
        $words = implode(",", $words);
        $pages = [];
        foreach ($ta as $i) {
            $pages[] = [
                "heading" => $this->pages->heading($i),
                "url" => $request->sn() . "?" . $this->pages->url($i) . "&search=" . urlencode($words),
            ];
        }
        return Response::create($this->view->render("search_results", [
            "search_term" => $request->search(),
            "pages" => $pages,
        ]))->withTitle($this->view->text("search_title"));
    }

    /**
     * Returns a list of all pages that contain all words in a co-content.
     * If $name === NULL the main content will be searched.
     *
     * @param string|null $name  A co-content name.
     * @param string[]  $words An array of words.
     *
     * @return int[]
     *
     * @global array The content of the pages.
     * @global array The configuration of the core.
     */
    private function searchContent($name, array $words)
    {
        if ($name === null) {
            $cocos = $this->pages->contents();
        } else {
            $cocos = $this->cocoService->findAll($name);
        }
        $ta = array();
        foreach ($cocos as $i => $coco) {
            if (!$this->pages->isHidden((int) $i)) {
                if ($this->doSearch($words, $coco)) {
                    $ta[] = $i;
                }
            }
        }
        return $ta;
    }

    /**
     * Returns whether all words are contained in a text.
     *
     * @param string[] $words An array of words.
     * @param string $text  A text to search in.
     *
     * @return bool
     */
    private function doSearch(array $words, $text)
    {
        $text = strip_tags($this->xhStuff->evaluateScripting($text));
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        $text = utf8_strtolower($text);
        foreach ($words as $word) {
            if (strpos($text, utf8_strtolower($word)) === false) {
                return false;
            }
        }
        return true;
    }
}
