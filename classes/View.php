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

namespace Coco;

class View
{
    /** @var array<string,string> */
    private $lang;

    /** @var string */
    private $templateDir;

    /**
     * @param array<string,string> $lang
     * @param string $templateDir
     */
    public function __construct(array $lang, $templateDir)
    {
        $this->lang = $lang;
        $this->templateDir = $templateDir;
    }

    /**
     * @param string $key
     * @return string
     */
    public function text($key)
    {
        $args = func_get_args();
        array_shift($args);
        return $this->esc(vsprintf($this->lang[$key], $args));
    }

    /**
     * @param string $key
     * @param int $count
     * @return string
     */
    public function plural($key, $count)
    {
        if ($count == 0) {
            $key .= '_0';
        } else {
            $key .= XH_numberSuffix($count);
        }
        $args = func_get_args();
        array_shift($args);
        return $this->esc(vsprintf($this->lang[$key], $args));
    }

    /**
     * @param string $_template
     * @param array<string,mixed> $_data
     * @return void
     */
    public function render($_template, array $_data)
    {
        extract($_data);
        include "{$this->templateDir}/$_template.php";
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public function esc($value)
    {
        if ($value instanceof HtmlString) {
            return $value;
        } else {
            return XH_hsc($value);
        }
    }
}
