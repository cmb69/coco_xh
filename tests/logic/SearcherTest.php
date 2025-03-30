<?php

namespace Coco\Logic;

use PHPUnit\Framework\TestCase;

class SearcherTest extends TestCase
{
    /** @dataProvider searchTerms */
    public function testParseSearchTerm(string $searchTerm, array $expected): void
    {
        $words = Searcher::parseSearchTerm($searchTerm);
        $this->assertEquals($expected, $words);
    }

    public function searchTerms(): array
    {
        return [
            ["", []],
            [" ", []],
            ["one two", ["one", "two"]],
            ["  one  two  ", ["one", "two"]],
        ];
    }
}
