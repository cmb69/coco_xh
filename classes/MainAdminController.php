<?php

/**
 * Copyright 2012-2017 Christoph M. Becker
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

class MainAdminController
{
    /**
     * @var string
     */
    private $pluginFolder;

    /**
     * @var array
     */
    private $lang;

    /**
     * @var XH_CSRFProtection
     */
    private $csrfProtector;

    public function __construct()
    {
        global $pth, $plugin_tx, $_XH_csrfProtection;

        $this->pluginFolder = "{$pth['folder']['plugins']}coco/";
        $this->lang = $plugin_tx['coco'];
        $this->csrfProtector = $_XH_csrfProtection;
    }

    public function defaultAction()
    {
        global $sn, $tx;

        $view = new View('admin');
        $view->csrfTokenInput = new HtmlString($this->csrfProtector->tokenInput());
        $view->url = "$sn?&coco&admin=plugin_main";
        $view->deleteIcon = "{$this->pluginFolder}images/delete.png";
        $view->alt = ucfirst($tx['action']['delete']);
        $cocos = [];
        foreach (Coco_cocos() as $coco) {
            $message = addcslashes(sprintf($this->lang['confirm_delete'], $coco), "\n\r\t\\");
            $cocos[] = (object) ['name' => $coco, 'message' => $message];
        }
        $view->cocos = $cocos;
        $view->render();
    }

    public function deleteAction()
    {
        $this->csrfProtector->check();
        $name = $_POST['coco_name'];
        $fns = glob(Coco_dataFolder().'????????_??????_' . $name . '.htm');
        foreach ($fns as $fn) {
            if (!unlink($fn)) {
                e('cntdelete', 'backup', $fn);
            }
        }
        if (!unlink(Coco_dataFolder() . $name . '.htm')) {
            e('cntdelete', 'file', $fn);
        }
        $this->defaultAction();
    }
}
