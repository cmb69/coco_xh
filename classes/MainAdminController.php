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
        $action = $request->action();
        switch ($action) {
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
            "cocos" => $this->cocoService->findAllNames(),
        ]));
    }

    private function confirmDelete(Request $request): Response
    {
        return Response::create($this->view->render("confirm", [
            "action" => $request->sn() . "?coco&admin=plugin_main&action=do_delete",
            "cocos" => $_GET["coco_name"],
            "csrf_token" => $this->csrfProtector->token(),
        ]));
    }

    private function delete(Request $request): Response
    {
        $this->csrfProtector->check();
        $post = $request->posts()->deleteCocos();
        if ($post === null) {
            return $this->show($request);
        }
        $o = "";
        foreach ($post["names"] as $name) {
            $result = $this->cocoService->delete((string) $name);
            foreach ($result as $filename) {
                $o .= $this->view->message("fail", "error_delete", $filename);
            }
        }
        if ($o !== "") {
            return Response::create($o)->merge($this->show($request));
        }
        return Response::redirect(CMSIMPLE_URL . "?coco&admin=plugin_main");
    }
}
