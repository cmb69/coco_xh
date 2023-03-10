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

use Coco\Infra\CocoService;
use Coco\Infra\CsrfProtector;
use Coco\Infra\Html;
use Coco\Infra\Pages;
use Coco\Infra\Request;
use Coco\Infra\Response;
use Coco\Infra\XhStuff;
use Coco\Infra\View;
use Coco\Logic\Util;

class MainController
{
    /** @var CocoService */
    private $cocoService;

    /** @var CsrfProtector */
    private $csrfProtector;

    /** @var Pages */
    private $pages;

    /** @var XhStuff */
    private $xhStuff;

    /** @var View */
    private $view;

    public function __construct(
        CocoService $cocoService,
        CsrfProtector $csrfProtector,
        Pages $pages,
        XhStuff $xhStuff,
        View $view
    ) {
        $this->cocoService = $cocoService;
        $this->csrfProtector = $csrfProtector;
        $this->pages = $pages;
        $this->xhStuff = $xhStuff;
        $this->view = $view;
    }

    public function __invoke(Request $request, string $name, string $config, string $height): Response
    {
        if (!Util::isValidCocoName($name)) {
            return Response::create($this->view->message("fail", "error_invalid_name") . "\n");
        }
        if ($request->s() < 0 || $request->s() >= $this->pages->count()) {
            return Response::create("");
        }
        if (!$request->adm() || !$request->edit()) {
            return $this->show($request, $name);
        }
        if ($request->cocoText($name) === null) {
            return $this->edit($request, $name, $config, $height);
        }
        return $this->update($request, $name, $config, $height);
    }

    private function show(Request $request, string $name): Response
    {
        $text = $this->xhStuff->evaluateScripting((string) $this->cocoService->find($name, $request->s()));
        if ($request->search() !== "") {
            $words = Util::parseSearchTerm($request->search());
            $text = $this->xhStuff->highlightSearchWords($words, $text);
        }
        return Response::create($text);
    }

    private function edit(Request $request, string $name, string $config, string $height): Response
    {
        $content = $this->cocoService->find($name, $request->s());
        return Response::create($this->renderEditor($name, $config, $height, $content));
    }

    private function update(Request $request, string $name, string $config, string $height): Response
    {
        $this->csrfProtector->check();
        $text = $request->cocoText($name);
        assert($text !== null);
        if ($this->cocoService->save($name, $request->s(), $text)) {
            return Response::redirect(CMSIMPLE_URL . "?" . $request->queryString());
        }
        return Response::create(
            $this->view->message("fail", "error_save", $this->cocoService->filename($name))
            . $this->renderEditor($name, $config, $height, $text)
        );
    }

    private function renderEditor(string $name, string $config, string $height, string $content): string
    {
        $id = 'coco_text_' . $name;
        $editor = $this->xhStuff->replaceEditor($id, $config);
        return $this->view->render("edit_form", [
            "id" => $id,
            "name" => $name,
            "style" => 'width:100%; height:' . $height,
            "content" => $content,
            "editor" => $editor !== false ? new Html($editor) : false,
            "csrf_token" => $this->csrfProtector->token(),
        ]);
    }
}
