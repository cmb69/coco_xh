<?php

/**
 * Copyright 2017-2021 Christoph M. Becker
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

namespace Coco\Infra;

class SystemCheckService
{
    /**
     * @var string
     */
    private $pluginFolder;

    /**
     * @var array<string,string>
     */
    private $lang;

    /** @var CocoService */
    private $cocoService;

    public function __construct(CocoService $cocoService)
    {
        global $pth, $plugin_tx;

        $this->pluginFolder = "{$pth['folder']['plugins']}coco";
        $this->lang = $plugin_tx['coco'];
        $this->cocoService = $cocoService;
    }

    /**
     * @return array<array{state:string,label:string,stateLabel:string}>
     */
    public function getChecks()
    {
        return array(
            $this->checkPhpVersion('7.0.0'),
            $this->checkXhVersion('1.7.0'),
            $this->checkWritability("$this->pluginFolder/css/"),
            $this->checkWritability("$this->pluginFolder/languages/"),
            $this->checkWritability($this->cocoService->dataDir())
        );
    }

    /**
     * @param string $version
     * @return array{state:string,label:string,stateLabel:string}
     */
    private function checkPhpVersion($version)
    {
        $state = version_compare(PHP_VERSION, $version, 'ge') ? 'success' : 'fail';
        $label = sprintf($this->lang['syscheck_phpversion'], $version);
        $stateLabel = $this->lang["syscheck_$state"];
        return compact('state', 'label', 'stateLabel');
    }

    /**
     * @param string $version
     * @return array{state:string,label:string,stateLabel:string}
     */
    private function checkXhVersion($version)
    {
        $state = version_compare(CMSIMPLE_XH_VERSION, "CMSimple_XH $version", 'ge') ? 'success' : 'fail';
        $label = sprintf($this->lang['syscheck_xhversion'], $version);
        $stateLabel = $this->lang["syscheck_$state"];
        return compact('state', 'label', 'stateLabel');
    }

    /**
     * @param string $folder
     * @return array{state:string,label:string,stateLabel:string}
     */
    private function checkWritability($folder)
    {
        $state = is_writable($folder) ? 'success' : 'warning';
        $label = sprintf($this->lang['syscheck_writable'], $folder);
        $stateLabel = $this->lang["syscheck_$state"];
        return compact('state', 'label', 'stateLabel');
    }
}
