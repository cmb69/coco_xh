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
use Coco\Infra\Repository;
use Coco\Infra\Request;
use Coco\Infra\View;
use Coco\Value\Response;

class CocoAdmin
{
    /** @var Repository */
    private $repository;

    /** @var CsrfProtector */
    private $csrfProtector;

    /** @var View */
    private $view;

    public function __construct(Repository $repository, CsrfProtector $csrfProtector, View $view)
    {
        $this->repository = $repository;
        $this->csrfProtector = $csrfProtector;
        $this->view = $view;
    }

    public function __invoke(Request $request): Response
    {
        switch ($request->cocoAdminAction()) {
            default:
                return $this->show($request);
            case "delete":
                return $this->confirmDelete($request);
            case "do_delete":
                return $this->delete($request);
        }
    }

    private function show(Request $request): Response
    {
        return Response::create($this->view->render("admin", [
            "action" => $request->sn(),
            "cocos" => $this->repository->findAllNames(),
        ]))->withTitle("Coco – " . $this->view->text("menu_main"));
    }

    /** @param list<array{key:string,arg:string}> $errors */
    private function confirmDelete(Request $request, array $errors = []): Response
    {
        return Response::create($this->view->render("confirm", [
            "errors" => $errors,
            "cocos" => $request->cocoNames(),
            "csrf_token" => $this->csrfProtector->token(),
        ]))->withTitle("Coco – " . $this->view->text("menu_main"));
    }

    private function delete(Request $request): Response
    {
        $this->csrfProtector->check();
        $errors = [];
        foreach ($request->cocoNames() as $name) {
            foreach ($this->repository->findAllBackups($name) as $backup) {
                if (!$this->repository->delete(...$backup)) {
                    $errors[] = ["key" => "error_delete", "arg" => $this->repository->filename(...$backup)];
                }
            }
            if (!$this->repository->delete($name)) {
                $errors[] = ["key" => "error_delete", "arg" => $this->repository->filename($name)];
            }
        }
        if ($errors) {
            return $this->confirmDelete($request, $errors);
        }
        return Response::redirect(CMSIMPLE_URL . "?coco&admin=plugin_main");
    }
}
