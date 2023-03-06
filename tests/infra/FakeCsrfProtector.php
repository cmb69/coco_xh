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

use Exception;

class FakeCsrfProtector extends CsrfProtector
{
    public function __construct($options = [])
    {
        $this->xhCsrfProtection = new FakeCSRFProtection($options);
    }
}

class FakeCSRFProtection
{
    private $options;

    public function __construct(array $options)
    {
        $this->options = $options;
    }

    public function tokenInput()
    {
        return "<input type=\"hidden\" name=\"xh_csrf_token\" value=\"eee5e668b3bcc9b71a9e4cc1aa76393f\">";
    }

    public function check()
    {
        if (($this->options["check"] ?? false) === true) {
            throw new Exception("CSRF check failed!");
        }
    }
}
