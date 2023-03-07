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
        if (($_POST["coco_do"] ?? null) === $request->action() && $request->forms()->deleteCocos() !== null) {
            return $this->delete($request);
        }
        if ($request->action() === "delete" && $request->forms()->deleteCocos() !== null) {
            return $this->confirmDelete($request);
        }
        return $this->show($request);
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
        $form = $request->forms()->deleteCocos();
        assert($form !== null);
        return Response::create($this->view->render("confirm", [
            "errors" => $errors,
            "cocos" => $form["names"],
            "csrf_token" => $this->csrfProtector->token(),
        ]))->withTitle("Coco – " . $this->view->text("menu_main"));
    }

    private function delete(Request $request): Response
    {
        $this->csrfProtector->check();
        $form = $request->forms()->deleteCocos();
        assert($form !== null);
        $errors = [];
        foreach ($form["names"] as $name) {
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
