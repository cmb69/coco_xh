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

    /**
     * @var CsrfProtector
     */
    private $csrfProtector;

    /** @var View */
    private $view;

    /**
     * @param string $name
     * @param string $config
     * @param string $height
     */
    public function __construct($name, $config, $height, CsrfProtector $csrfProtector, View $view)
    {
        $this->name = $name;
        $this->config = $config;
        $this->height = $height;
        $this->csrfProtector = $csrfProtector;
        $this->view = $view;
    }

    /**
     * @return void
     */
    public function defaultAction()
    {
        global $s;

        $text = evaluate_scripting((string) Plugin::get($this->name, $s));
        if (isset($_GET['search'])) {
            $class = 'xh_find';
            $search = urldecode($_GET['search']);
            $search = XH_hsc($search);
            $words = explode(',', $search);
            $func = function($w) {
                return "/" . preg_quote($w, "/") . "(?!([^<]+)?>)/isU";
            };
            $words = array_map($func, $words);
            $text = preg_replace(
                $words, '<span class="' . $class . '">\\0</span>', $text
            );
        }
        echo $text;
    }

    /**
     * @return void
     */
    public function editAction()
    {
        global $s, $tx;

        if (isset($_POST['coco_text_' . $this->name])) {
            $this->csrfProtector->check();
            Plugin::set($this->name, $s, $_POST['coco_text_' . $this->name]);
        }
        $id = 'coco_text_' . $this->name;
        $editor = editor_replace($id, $this->config);
        $this->view->render("edit-form", [
            "id" => $id,
            "name" => $this->name,
            "style" => 'width:100%; height:' . $this->height,
            "content" => Plugin::get($this->name, $s),
            "editor" => $editor !== false ? new HtmlString($editor) : false,
            "saveLabel" => ucfirst($tx['action']['save']),
            "csrfTokenInput" => new HtmlString($this->csrfProtector->tokenInput()),
        ]);
    }
}
