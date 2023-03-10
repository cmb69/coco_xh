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

use Coco\Value\Response;

/** @codeCoverageIgnore */
class Responder
{
    /** @return string|never */
    public static function respond(Response $response)
    {
        global $title;

        if ($response->location() !== null) {
            while (ob_get_level()) {
                ob_end_clean();
            }
            header("Location: " . $response->location(), true, 303);
            exit;
        }
        if ($response->title() !== null) {
            $title = $response->title();
        }
        return $response->output();
    }
}
