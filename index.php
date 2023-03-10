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

if (!defined("CMSIMPLE_XH_VERSION")) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

use Coco\Dic;
use Coco\Infra\Request;
use Coco\Infra\Responder;

const COCO_VERSION = "2.0-dev";

/**
 * @param string $name
 * @param string|false $config
 * @param string $height
 * @return string
 */
function coco($name, $config = false, $height = "100%")
{
    return Responder::respond(Dic::makeCoco()(Request::current(), $name, (string) $config, $height));
}

/**
 * @var XH\PageDataRouter $pd_router
 * @var string $f
 * @var string $o
 */

$pd_router->add_interest("coco_id");

$o .= Responder::respond(Dic::makeMain()(Request::current()));
