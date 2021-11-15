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

    /**
     * @param int $maxBackups
     */
    public function __construct($maxBackups, CocoService $cocoService)
    {
        $this->maxBackups = $maxBackups;
        $this->cocoService = $cocoService;
    }

    /**
     * @param string $backupDate
     * @return void
     */
    public function execute($backupDate)
    {
        global $tx;

        $dir = $this->cocoService->dataDir() . "/";
        $o = '';
        foreach ($this->cocoService->findAll() as $coco) {
            $fn = $dir . $backupDate . '_' . $coco . '.htm';
            if (copy($dir . $coco . '.htm', $fn)) {
                $o .= XH_message(
                    'info',
                    ucfirst($tx['filetype']['backup']) . ' ' . $fn . ' '
                    . $tx['result']['created']
                ) . PHP_EOL;
                $bus = glob($dir . '????????_??????_' . $coco . '.htm') ?: [];
                for ($i = 0; $i < count($bus) - $this->maxBackups; $i++) {
                    if (unlink($bus[$i])) {
                        $o .= XH_message(
                            'info',
                            ucfirst($tx['filetype']['backup']) . ' ' . $bus[$i]
                            . ' ' . $tx['result']['deleted']
                        ) . PHP_EOL;
                    } else {
                        e('cntdelete', 'backup', $bus[$i]);
                    }
                }
            } else {
                e('cntsave', 'backup', $fn);
            }
        }
        echo $o;
    }
}
