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

use Plib\HtmlView as View;

class InfoController
{
    /** @var SystemCheckService */
    private $systemCheckService;

    /** @var View */
    private $view;

    public function __construct(SystemCheckService $systemCheckService, View $view)
    {
        $this->systemCheckService = $systemCheckService;
        $this->view = $view;
    }

    /**
     * @return void
     */
    public function defaultAction()
    {
        echo $this->view->render("info", [
            "version" => Plugin::VERSION,
            "checks" => $this->systemCheckService->getChecks(),
        ]);
    }
}
