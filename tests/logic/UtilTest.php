<?php

namespace Coco\Logic;

use PHPUnit\Framework\TestCase;

class UtilTest extends TestCase
{
    /** @dataProvider cocoNames */
    public function testIsValidCocoName(string $name, bool $expected): void
    {
        $result = Util::isValidCocoName($name);
        $this->assertEquals($expected, $result);
    }

    public function cocoNames(): array
    {
        return [
            ["hello", true],
            ["hey you", false],
            ["Hello", false],
            ["hey-you", false],
        ];
    }

    /** @dataProvider cocoFilenames */
    public function testIsCocoFilename(string $filename, bool $expected): void
    {
        $result = Util::isCocoFilename($filename);
        $this->assertEquals($expected, $result);
    }

    public function cocoFilenames(): array
    {
        return [
            ["test.htm", true],
            ["something else.htm", false],
            ["test.txt", false],
            ["20230310_131500_test.htm", true],
        ];
    }

    /** @dataProvider backupFilenames */
    public function testIsBackup(string $filename, ?string $cocoName, bool $expected): void
    {
        $result = Util::isBackup($filename, $cocoName);
        $this->assertEquals($expected, $result);
    }

    public function backupFilenames(): array
    {
        return [
            ["20230310_120000_test.htm", null, true],
            ["20230310_120000_test.txt", null, false],
            ["20230310_120000_test.htm", "test", true],
            ["20230310_120000_foo.htm", "test", false],
        ];
    }

    public function testBackupName(): void
    {
        $backupName = Util::backupName("20230310_221700_test.htm");
        $this->assertEquals(["test", "20230310_221700"], $backupName);
    }

    /** @dataProvider backupPrefixes */
    public function testBackupPrefix(int $timestamp, string $expected): void
    {
        $result = Util::backupPrefix($timestamp);
        $this->assertEquals($expected, $result);
    }

    public function backupPrefixes(): array
    {
        return [
            [strtotime("2023-03-10T13:28:00"), "20230310_132800"],
        ];
    }

    /** @dataProvider cocoContents */
    public function testCocoContent(string $content, string $id, string $expected): void
    {
        $result = Util::cocoContent($content, $id);
        $this->assertEquals($expected, $result);
    }

    public function cocoContents(): array
    {
        $content = <<<EOT
            <h1 id="123456">Blah</h1>
            <p>some co-content</p>
            <h2 id="234567">Yada Yada</h2>
            <p>some other co-content</p>
            <h1>Blub</h1>
            <p>some content without ID</p>
            EOT;
        return [
            [$content, "123456", "<p>some co-content</p>"],
            [$content, "234567", "<p>some other co-content</p>"],
            [$content, "345678", ""],
        ];
    }
}

