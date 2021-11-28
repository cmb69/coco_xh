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

class MainController
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $config;

    /**
     * @var string
     */
    private $height;

    /** @var CocoService */
    private $cocoService;

    /**
     * @var CsrfProtector|null
     */
    private $csrfProtector;

    /** @var View */
    private $view;

    /**
     * @param string $name
     * @param string $config
     * @param string $height
     * @param CsrfProtector|null $csrfProtector
     */
    public function __construct(
        $name,
        $config,
        $height,
        CocoService $cocoService,
        $csrfProtector,
        View $view
    ) {
        $this->name = $name;
        $this->config = $config;
        $this->height = $height;
        $this->cocoService = $cocoService;
        $this->csrfProtector = $csrfProtector;
        $this->view = $view;
    }

    /**
     * @return void
     */
    public function defaultAction()
    {
        global $s;

        $text = evaluate_scripting((string) $this->cocoService->find($this->name, $s));
        if (isset($_GET['search'])) {
            $search = XH_hsc(trim(preg_replace('/\s+/u', ' ', ($_GET['search']))));
            $words = explode(' ', $search);
            $text = XH_highlightSearchWords($words, $text);
        }
        echo $text;
    }

    /**
     * @return void
     */
    public function editAction()
    {
        global $s, $tx;

        assert($this->csrfProtector !== null);
        if (isset($_POST['coco_text_' . $this->name])) {
            $this->csrfProtector->check();
            $content = $_POST['coco_text_' . $this->name];
            if (!$this->cocoService->save($this->name, $s, $content)) {
                echo $this->view->message("fail", "error_save", $this->cocoService->filename($this->name));
            }
        } else {
            $content = $this->cocoService->find($this->name, $s);
        }
        $id = 'coco_text_' . $this->name;
        $editor = editor_replace($id, $this->config);
        echo $this->view->render("edit-form", [
            "id" => $id,
            "name" => $this->name,
            "style" => 'width:100%; height:' . $this->height,
            "content" => $content,
            "editor" => $editor !== false ? new HtmlString($editor) : false,
            "csrfTokenInput" => new HtmlString($this->csrfProtector->tokenInput()),
        ]);
    }
}
