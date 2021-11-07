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

class MainAdminController
{
    /**
     * @var string
     */
    private $pluginFolder;

    /**
     * @var array<string,string>
     */
    private $lang;

    /**
     * @var CsrfProtector
     */
    private $csrfProtector;

    /** @var View */
    private $view;

    public function __construct(CsrfProtector $csrfProtector, View $view)
    {
        global $pth, $plugin_tx;

        $this->pluginFolder = "{$pth['folder']['plugins']}coco/";
        $this->lang = $plugin_tx['coco'];
        $this->csrfProtector = $csrfProtector;
        $this->view = $view;
    }

    /**
     * @return void
     */
    public function defaultAction()
    {
        global $sn, $tx;

        $cocos = [];
        foreach (Plugin::cocos() as $coco) {
            $message = addcslashes(sprintf($this->lang['confirm_delete'], $coco), "\n\r\t\\");
            $cocos[] = (object) ['name' => $coco, 'message' => $message];
        }
        $this->view->render("admin", [
            "csrfTokenInput" => new HtmlString($this->csrfProtector->tokenInput()),
            "url" => "$sn?&coco&admin=plugin_main",
            "deleteIcon" => "{$this->pluginFolder}images/delete.png",
            "alt" => ucfirst($tx['action']['delete']),
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
        $fns = glob(Plugin::dataFolder().'????????_??????_' . $name . '.htm');
        foreach ($fns as $fn) {
            if (!unlink($fn)) {
                e('cntdelete', 'backup', $fn);
            }
        }
        $fn = Plugin::dataFolder() . $name . '.htm';
        if (!unlink($fn)) {
            e('cntdelete', 'file', $fn);
        }
        $this->defaultAction();
    }
}
