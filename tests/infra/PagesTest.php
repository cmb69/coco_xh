<?php

namespace Coco\Infra;

use PHPUnit\Framework\TestCase;
use XH\Pages as XhPages;


class PagesTest extends TestCase
{
    public function testContents(): void
    {
        $sut = $this->sut();
        $actual = $sut->contents();
        $this->assertEquals(["foo", "bar", "baz"], $actual);
    }

    private function sut(): Pages
    {
        $xhPages = $this->createMock(XhPages::class);
        $xhPages->method("getCount")->willReturn(3);
        $xhPages->method("content")->willReturnMap([[0, "foo"], [1, "bar"], [2, "baz"]]);
        return new Pages($xhPages);
    }
}
