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

final class BackupController
{
    /** @var int */
    private $maxBackups;

    /** @var CocoService */
    private $cocoService;

    /** @var View */
    private $view;

    /**
     * @param int $maxBackups
     */
    public function __construct($maxBackups, CocoService $cocoService, View $view)
    {
        $this->maxBackups = $maxBackups;
        $this->cocoService = $cocoService;
        $this->view = $view;
    }

    /**
     * @param string $backupDate
     * @return void
     */
    public function execute($backupDate)
    {
        $dir = $this->cocoService->dataDir() . "/";
        foreach ($this->cocoService->findAllNames() as $coco) {
            $fn = $dir . $backupDate . '_' . $coco . '.htm';
            if (copy($dir . $coco . '.htm', $fn)) {
                echo $this->view->message("info", "info_created", $fn);
                $bus = glob($dir . '????????_??????_' . $coco . '.htm') ?: [];
                for ($i = 0; $i < count($bus) - $this->maxBackups; $i++) {
                    if (unlink($bus[$i])) {
                        echo $this->view->message("info", "info_deleted", $bus[$i]);
                    } else {
                        echo $this->view->message("fail", "error_delete", $bus[$i]);
                    }
                }
            } else {
                echo $this->view->message("fail", "error_save", $fn);
            }
        }
    }
}
