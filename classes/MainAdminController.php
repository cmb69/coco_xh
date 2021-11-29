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

use Plib\HtmlString;
use Plib\HtmlView as View;
use XH\CSRFProtection as CsrfProtector;

class MainAdminController
{
    /** @var CocoService */
    private $cocoService;

    /**
     * @var CsrfProtector
     */
    private $csrfProtector;

    /** @var View */
    private $view;

    public function __construct(CocoService $cocoService, CsrfProtector $csrfProtector, View $view)
    {
        $this->cocoService = $cocoService;
        $this->csrfProtector = $csrfProtector;
        $this->view = $view;
    }

    /**
     * @return void
     */
    public function defaultAction()
    {
        global $sn;

        $cocos = [];
        foreach ($this->cocoService->findAllNames() as $coco) {
            $message = new HtmlString(
                addcslashes($this->view->text('confirm_delete', new HtmlString($coco)), "\n\r\t\\")
            );
            $cocos[] = (object) ['name' => $coco, 'message' => $message];
        }
        echo $this->view->render("admin", [
            "csrfTokenInput" => new HtmlString($this->csrfProtector->tokenInput()),
            "url" => (new Url($sn))->withPage("coco")->withParam("admin", "plugin_main"),
            "cocos" => $cocos,
        ]);
    }

    /**
     * @return void
     */
    public function deleteAction()
    {
        $this->csrfProtector->check();
        $name = $_POST['coco_name'];
        $result = $this->cocoService->delete($name);
        foreach ($result as $filename => $success) {
            if (!$success) {
                echo $this->view->message("fail", "error_delete", $filename);
            }
        }
        $this->defaultAction();
    }
}
