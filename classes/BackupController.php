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

use Coco\Infra\Backups;
use Coco\Infra\CocoService;
use Coco\Infra\Request;
use Coco\Infra\View;

class BackupController
{
    /** @var int */
    private $maxBackups;

    /** @var CocoService */
    private $cocoService;

    /** @var Backups */
    private $backups;

    /** @var View */
    private $view;

    /**
     * @param int $maxBackups
     */
    public function __construct($maxBackups, CocoService $cocoService, Backups $backups, View $view)
    {
        $this->maxBackups = $maxBackups;
        $this->cocoService = $cocoService;
        $this->backups = $backups;
        $this->view = $view;
    }

    public function __invoke(Request $request): string
    {
        $o = "";
        foreach ($this->cocoService->findAllNames() as $coco) {
            $o .= $this->backup($coco, date("Ymd_His", (int) $request->server("REQUEST_TIME")));
        }
        return $o;
    }

    private function backup(string $coconame, string $backupDate): string
    {
        $dir = $this->cocoService->dataDir() . "/";
        if (!$this->backups->create($dir, $coconame, $backupDate)) {
            return $this->view->message("fail", "error_save", $this->backups->filename($dir, $coconame, $backupDate));
        }
        $o = $this->view->message("info", "info_created", $this->backups->filename($dir, $coconame, $backupDate));
        $backups = $this->backups->all($dir, $coconame);
        for ($i = 0; $i < count($backups) - $this->maxBackups; $i++) {
            if ($this->backups->delete($backups[$i])) {
                $o .= $this->view->message("info", "info_deleted", $backups[$i]);
            } else {
                $o .= $this->view->message("fail", "error_delete", $backups[$i]);
            }
        }
        return $o;
    }
}
