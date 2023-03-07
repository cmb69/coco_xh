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
use Coco\Infra\CsrfProtector;
use Coco\Infra\Request;
use Coco\Infra\Response;
use Coco\Infra\View;

class MainAdminController
{
    /** @var CocoService */
    private $cocoService;

    /** @var CsrfProtector */
    private $csrfProtector;

    /** @var View */
    private $view;

    public function __construct(CocoService $cocoService, CsrfProtector $csrfProtector, View $view)
    {
        $this->cocoService = $cocoService;
        $this->csrfProtector = $csrfProtector;
        $this->view = $view;
    }

    public function __invoke(Request $request): Response
    {
        if ($request->action() !== "delete") {
            return $this->show($request);
        }
        if (!isset($_POST["coco_do"])) {
            return $this->confirmDelete($request);
        }
        return $this->delete($request);
    }

    private function show(Request $request): Response
    {
        return Response::create($this->view->render("admin", [
            "action" => $request->sn(),
            "cocos" => $this->cocoService->findAllNames(),
        ]))->withTitle("Coco – " . $this->view->text("menu_main"));
    }

    /** @param list<array{key:string,arg:string}> $errors */
    private function confirmDelete(Request $request, array $errors = []): Response
    {
        return Response::create($this->view->render("confirm", [
            "errors" => $errors,
            "cocos" => $_GET["coco_name"],
            "csrf_token" => $this->csrfProtector->token(),
        ]))->withTitle("Coco – " . $this->view->text("menu_main"));
    }

    private function delete(Request $request): Response
    {
        $this->csrfProtector->check();
        $post = $request->forms()->deleteCocos();
        if ($post === null) {
            return $this->show($request);
        }
        $errors = [];
        foreach ($post["names"] as $name) {
            $result = $this->cocoService->delete((string) $name);
            foreach ($result as $filename) {
                $errors[] = ["key" => "error_delete", "arg" => $filename];
            }
        }
        if ($errors) {
            return $this->confirmDelete($request, $errors);
        }
        return Response::redirect(CMSIMPLE_URL . "?coco&admin=plugin_main");
    }
}
