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
use Coco\Infra\SystemChecker;
use Coco\Infra\View;

class PluginInfo
{
    /** @var string */
    private $pluginFolder;

    /** @var CocoService */
    private $cocoService;

    /** @var SystemChecker */
    private $systemChecker;

    /** @var View */
    private $view;

    public function __construct(
        string $pluginFolder,
        CocoService $cocoService,
        SystemChecker $systemChecker,
        View $view
    ) {
        $this->pluginFolder = $pluginFolder;
        $this->cocoService = $cocoService;
        $this->systemChecker = $systemChecker;
        $this->view = $view;
    }

    public function __invoke(): string
    {
        return $this->view->render("info", [
            "version" => COCO_VERSION,
            "checks" => $this->getChecks(),
        ]);
    }

    /**
     * @return array<array{state:string,key:string,arg:string,state_key:string}>
     */
    public function getChecks()
    {
        return array(
            $this->checkPhpVersion('7.0.0'),
            $this->checkXhVersion('1.7.0'),
            $this->checkWritability($this->pluginFolder . "css/"),
            $this->checkWritability($this->pluginFolder . "languages/"),
            $this->checkWritability($this->cocoService->dataDir())
        );
    }

    /**
     * @param string $version
     * @return array{state:string,key:string,arg:string,state_key:string}
     */
    private function checkPhpVersion($version)
    {
        $state = $this->systemChecker->checkVersion(PHP_VERSION, $version) ? 'success' : 'fail';
        return [
            "state" => $state,
            "key" => "syscheck_phpversion",
            "arg" => $version,
            "state_key" => "syscheck_$state",
        ];
    }

    /**
     * @param string $version
     * @return array{state:string,key:string,arg:string,state_key:string}
     */
    private function checkXhVersion($version)
    {
        $state = $this->systemChecker->checkVersion(CMSIMPLE_XH_VERSION, "CMSimple_XH $version") ? 'success' : 'fail';
        return [
            "state" => $state,
            "key" => "syscheck_xhversion",
            "arg" => $version,
            "state_key" => "syscheck_$state",
        ];
    }

    /**
     * @param string $folder
     * @return array{state:string,key:string,arg:string,state_key:string}
     */
    private function checkWritability($folder)
    {
        $state = $this->systemChecker->checkWritability($folder) ? 'success' : 'warning';
        return [
            "state" => $state,
            "key" => "syscheck_writable",
            "arg" => $folder,
            "state_key" => "syscheck_$state",
        ];
    }
}