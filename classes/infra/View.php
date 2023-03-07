<?php

/**
 * Copyright 2023 Christoph M. Becker
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

class View
{
    /** @var string */
    private $folder;

    /** @var array<string,string> */
    private $text;

    /** @param array<string,string> $text */
    public function __construct(string $folder, array $text)
    {
        $this->folder = $folder;
        $this->text = $text;
    }

    /** @param scalar $args */
    public function text(string $key, ...$args): string
    {
        return sprintf(XH_hsc($this->text[$key]), ...$args);
    }

    /** @param scalar $args */
    public function plural(string $key, int $count, ...$args): string
    {
        if ($count == 0) {
            $key .= '_0';
        } else {
            $key .= XH_numberSuffix($count);
        }
        return sprintf($this->esc($this->text[$key]), $count, ...$args);
    }

    /** @param scalar $args */
    public function message(string $type, string $key, ...$args): string
    {
        return XH_message($type, $this->text[$key], ...$args) . "\n";
    }

    public function esc(string $string): string
    {
        return XH_hsc($string);
    }

    /** @param array<string,mixed> $_data */
    public function render(string $_template, array $_data): string
    {
        array_walk_recursive($_data, function (&$value) {
            if (is_string($value)) {
                $value = XH_hsc($value);
            }
        });
        extract($_data);
        ob_start();
        include $this->folder . $_template . ".php";
        return (string) ob_get_clean();
    }
}
