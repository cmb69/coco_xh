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
    /** @var string */
    private $templateDir;

    /** @var array<string,string> */
    private $lang;

    /**
     * @param array<string,string> $lang
     * @param string $templateDir
     */
    public function __construct($templateDir, array $lang)
    {
        $this->templateDir = $templateDir;
        $this->lang = $lang;
    }

    /**
     * @param string $key
     * @param mixed $args
     * @return string
     */
    public function text($key, ...$args)
    {
        return $this->esc(vsprintf($this->lang[$key], $args));
    }

    /**
     * @param string $key
     * @param int $count
     * @param mixed $args
     * @return string
     */
    public function plural($key, $count, ...$args)
    {
        if ($count == 0) {
            $key .= '_0';
        } else {
            $key .= XH_numberSuffix($count);
        }
        return $this->esc(vsprintf($this->lang[$key], $args));
    }

    /**
     * @param string $type
     * @param string $key
     * @param mixed $args
     * @return void
     */
    public function message($type, $key, ...$args)
    {
        printf('<p class="xh_%s">%s</p>', $type, $this->text($key, ...$args));
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
     * @param string|HtmlString $value
     * @return string
     */
    public function esc($value)
    {
        if ($value instanceof HtmlString) {
            return $value->asString();
        } else {
            return XH_hsc($value);
        }
    }
}
