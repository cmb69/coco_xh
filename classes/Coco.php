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

use Coco\Infra\CsrfProtector;
use Coco\Infra\Html;
use Coco\Infra\Repository;
use Coco\Infra\Request;
use Coco\Infra\XhStuff;
use Coco\Infra\View;
use Coco\Logic\Util;
use Coco\Value\Response;

class Coco
{
    /** @var Repository */
    private $repository;

    /** @var CsrfProtector */
    private $csrfProtector;

    /** @var XhStuff */
    private $xhStuff;

    /** @var View */
    private $view;

    public function __construct(
        Repository $repository,
        CsrfProtector $csrfProtector,
        XhStuff $xhStuff,
        View $view
    ) {
        $this->repository = $repository;
        $this->csrfProtector = $csrfProtector;
        $this->xhStuff = $xhStuff;
        $this->view = $view;
    }

    public function __invoke(Request $request, string $name, string $config, string $height): Response
    {
        if (!Util::isValidCocoName($name)) {
            return Response::create($this->view->message("fail", "error_invalid_name") . "\n");
        }
        if ($request->s() < 0) {
            return Response::create("");
        }
        switch ($request->cocoAction($name)) {
            default:
                return $this->show($request, $name);
            case "edit":
                return $this->edit($request, $name, $config, $height);
            case "do_edit":
                return $this->update($request, $name, $config, $height);
        }
    }

    private function show(Request $request, string $name): Response
    {
        $content = $this->repository->find($name, $request->s());
        $content = $this->xhStuff->evaluateScripting($content);
        $search = $request->search();
        if ($search !== "") {
            $words = Util::parseSearchTerm($search);
            $content = $this->xhStuff->highlightSearchWords($words, $content);
        }
        return Response::create($content);
    }

    private function edit(Request $request, string $name, string $config, string $height): Response
    {
        $content = $this->repository->find($name, $request->s());
        return Response::create($this->renderEditor($name, $config, $height, $content));
    }

    private function update(Request $request, string $name, string $config, string $height): Response
    {
        $this->csrfProtector->check();
        $text = $request->cocoText($name);
        if ($this->repository->save($name, $request->s(), $text)) {
            return Response::redirect(CMSIMPLE_URL . "?" . $request->queryString());
        }
        return Response::create(
            $this->view->message("fail", "error_save", $this->repository->filename($name))
            . $this->renderEditor($name, $config, $height, $text)
        );
    }

    private function renderEditor(string $name, string $config, string $height, string $content): string
    {
        $id = "coco_text_$name";
        $editor = $this->xhStuff->replaceEditor($id, $config);
        return $this->view->render("edit_form", [
            "id" => $id,
            "name" => $name,
            "style" => "width:100%; height:$height",
            "content" => $content,
            "editor" => $editor !== false ? new Html($editor) : false,
            "csrf_token" => $this->csrfProtector->token(),
        ]);
    }
}
