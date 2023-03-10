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

use Coco\Infra\Repository;
use Coco\Infra\Request;
use Coco\Infra\View;
use Coco\Logic\Util;
use Coco\Value\Response;

class Main
{
    /** @var array<string,string> */
    private $conf;

    /** @var Repository */
    private $repository;

    /** @var View */
    private $view;

    /** @param array<string,string> $conf */
    public function __construct(array $conf, Repository $repository, View $view)
    {
        $this->conf = $conf;
        $this->repository = $repository;
        $this->view = $view;
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->logOut()) {
            return Response::create("");
        }
        $o = "";
        foreach ($this->repository->findAllNames() as $coco) {
            $o .= $this->backup($coco, Util::backupPrefix($request->requestTime()));
        }
        return Response::create($o);
    }

    private function backup(string $coconame, string $backupDate): string
    {
        if (!$this->repository->backup($coconame, $backupDate)) {
            return $this->view->message("fail", "error_save", $this->repository->filename($coconame, $backupDate));
        }
        $o = $this->view->message("info", "info_created", $this->repository->filename($coconame, $backupDate));
        $backups = $this->repository->findAllBackups($coconame);
        $backups = array_slice($backups, 0, count($backups) - (int) $this->conf['backup_numberoffiles']);
        foreach ($backups as $backup) {
            if ($this->repository->delete(...$backup)) {
                $o .= $this->view->message("info", "info_deleted", $this->repository->filename(...$backup));
            } else {
                $o .= $this->view->message("fail", "error_delete", $this->repository->filename(...$backup));
            }
        }
        return $o;
    }
}
