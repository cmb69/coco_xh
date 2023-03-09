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
use XH\CSRFProtection;

class CsrfProtector
{
    /** @var CSRFProtection|null */
    protected $xhCsrfProtection;

    public function __construct(?CSRFProtection $xhCsrfProtection = null)
    {
        global $_XH_csrfProtection;
        $this->xhCsrfProtection = $xhCsrfProtection ?? $_XH_csrfProtection;
    }

    public function token(): string
    {
        $input = $this->tokenInput();
        if (!preg_match('/value=\"([0-9a-z]+)\"/', $input, $matches)) {
            throw new Exception("CSRF protection is broken");
        }
        return $matches[1];
    }

    /** @codeCoverageIgnore */
    public function tokenInput(): string
    {
        assert($this->xhCsrfProtection !== null);
        return $this->xhCsrfProtection->tokenInput();
    }

    /**
     * @return void|never
     * @codeCoverageIgnore
     */
    public function check()
    {
        assert($this->xhCsrfProtection !== null);
        $this->xhCsrfProtection->check();
    }
}
