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

class FakeRequest extends Request
{
    private $options;

    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    public function sn(): string
    {
        return "/";
    }

    public function search(): string
    {
        return $this->options["search"] ?? "";
    }

    public function adm(): bool
    {
        return $this->options["adm"] ?? false;
    }

    public function edit(): bool
    {
        return $this->options["edit"] ?? false;
    }

    public function s(): int
    {
        return $this->options["s"] ?? 0;
    }

    public function action(): string
    {
        return $this->options["action"] ?? "";
    }

    public function server(string $key): ?string
    {
        return $this->options["server"][$key] ?? null;
    }
}
