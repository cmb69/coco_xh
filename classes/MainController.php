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
use Coco\Infra\Pages;
use Coco\Infra\Request;
use Coco\Infra\XhStuff;
use Coco\Infra\View;

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

    public function __invoke(Request $request, string $name, string $config, string $height): string
    {
        if (!preg_match('/^[a-z_0-9]+$/su', $name)) {
            return $this->view->message("fail", "error_invalid_name") . "\n";
        }
        if ($request->s() < 0 || $request->s() >= $this->pages->count()) {
            return "";
        }
        switch ($request->adm() && $request->edit()) {
            default:
                return $this->defaultAction($name);
            case true:
                return $this->editAction($name, $config, $height);
        }
    }

    private function defaultAction(string $name): string
    {
        global $s;

        $text = $this->xhStuff->evaluateScripting((string) $this->cocoService->find($name, $s));
        if (isset($_GET['search'])) {
            $search = XH_hsc(trim(preg_replace('/\s+/u', ' ', ($_GET['search']))));
            $words = explode(' ', $search);
            $text = $this->xhStuff->highlightSearchWords($words, $text);
        }
        return $text;
    }

    private function editAction(string $name, string $config, string $height): string
    {
        global $s;

        $o = "";
        if (isset($_POST['coco_text_' . $name])) {
            $this->csrfProtector->check();
            $content = $_POST['coco_text_' . $name];
            if (!$this->cocoService->save($name, $s, $content)) {
                $o .= $this->view->message("fail", "error_save", $this->cocoService->filename($name));
            }
        } else {
            $content = $this->cocoService->find($name, $s);
        }
        $id = 'coco_text_' . $name;
        $editor = $this->xhStuff->replaceEditor($id, $config);
        $o .= $this->view->render("edit-form", [
            "id" => $id,
            "name" => $name,
            "style" => 'width:100%; height:' . $height,
            "content" => $content,
            "editor" => $editor !== false ? $editor : false,
            "csrf_token" => $this->csrfProtector->token(),
        ]);
        return $o;
    }
}
