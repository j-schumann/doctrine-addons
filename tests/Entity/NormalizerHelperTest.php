<?php

declare(strict_types=1);

namespace Vrok\DoctrineAddons\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Vrok\DoctrineAddons\Entity\NormalizerHelper;

class NormalizerHelperTest extends TestCase
{
    public function testStripHtml(): void
    {
        self::assertSame(null, NormalizerHelper::stripHtml(null));
        self::assertSame('', NormalizerHelper::stripHtml(''));
        self::assertSame('', NormalizerHelper::stripHtml(' '));
        self::assertSame('test', NormalizerHelper::stripHtml("\ttest\r\n"));

        self::assertSame('a"bc123', NormalizerHelper::stripHtml('a&quot;bc<br>123'));
        self::assertSame('a bc123', NormalizerHelper::stripHtml('<span>a&nbsp;bc<br>123</span>'));
    }

    public function testGetTextLength(): void
    {
        self::assertSame(0, NormalizerHelper::getTextLength(null));
        self::assertSame(0, NormalizerHelper::getTextLength(''));
        self::assertSame(0, NormalizerHelper::getTextLength(' '));
        self::assertSame(4, NormalizerHelper::getTextLength("\ttest\r\n"));

        self::assertSame(6, NormalizerHelper::getTextLength('abc<br>123'));
        self::assertSame(6, NormalizerHelper::getTextLength('<span>abc<br>123</span>'));
    }

    public function testToHtml(): void
    {
        self::assertSame('', NormalizerHelper::toHtml(null));
        self::assertSame('', NormalizerHelper::toHtml(''));
        self::assertSame('', NormalizerHelper::toHtml(' '));
        self::assertSame('', NormalizerHelper::toHtml('<br />'));
        self::assertSame('test', NormalizerHelper::toHtml("\ttest\r\n"));

        self::assertSame('abc<br>123', NormalizerHelper::toHtml('abc<br>123'));
    }

    public function testToNullableHtml(): void
    {
        self::assertSame(null, NormalizerHelper::toNullableHtml(null));
        self::assertSame(null, NormalizerHelper::toNullableHtml(''));
        self::assertSame(null, NormalizerHelper::toNullableHtml(' '));
        self::assertSame(null, NormalizerHelper::toNullableHtml('<br />'));
        self::assertSame('test', NormalizerHelper::toNullableHtml("\ttest\r\n"));

        self::assertSame('abc<br>123', NormalizerHelper::toNullableHtml('abc<br>123'));
    }

    public function testToString(): void
    {
        self::assertSame('', NormalizerHelper::toString(null));
        self::assertSame('', NormalizerHelper::toString(''));
        self::assertSame('', NormalizerHelper::toString(' '));
        self::assertSame('<br />', NormalizerHelper::toString('<br />'));
        self::assertSame('test', NormalizerHelper::toString("\ttest\r\n"));

        self::assertSame('abc<br>123', NormalizerHelper::toString('abc<br>123'));
    }

    public function testToNullableString(): void
    {
        self::assertSame(null, NormalizerHelper::toNullableString(null));
        self::assertSame(null, NormalizerHelper::toNullableString(''));
        self::assertSame(null, NormalizerHelper::toNullableString(' '));
        self::assertSame('<br />', NormalizerHelper::toNullableString('<br />'));
        self::assertSame('test', NormalizerHelper::toNullableString("\ttest\r\n"));

        self::assertSame('abc<br>123', NormalizerHelper::toNullableString('abc<br>123'));
    }

    public function testToStringArray(): void
    {
        self::assertSame([], NormalizerHelper::toStringArray(null));
        self::assertSame([], NormalizerHelper::toStringArray([]));
        self::assertSame([], NormalizerHelper::toStringArray([' ', null, '']));
        self::assertEquals([new \stdClass()], NormalizerHelper::toNullableStringArray([new \stdClass()]));
        self::assertSame(['<br />'], NormalizerHelper::toStringArray(['<br />']));
        self::assertSame(['test'], NormalizerHelper::toStringArray(["\ttest\r\n"]));
    }

    public function testToNullableStringArray(): void
    {
        self::assertSame(null, NormalizerHelper::toNullableStringArray(null));
        self::assertSame(null, NormalizerHelper::toNullableStringArray([]));
        self::assertSame(null, NormalizerHelper::toNullableStringArray([' ', null, '']));
        self::assertEquals([new \stdClass()], NormalizerHelper::toNullableStringArray([new \stdClass()]));
        self::assertSame(['<br />'], NormalizerHelper::toNullableStringArray(['<br />']));
        self::assertSame(['test'], NormalizerHelper::toNullableStringArray(["\ttest\r\n"]));
    }

    public function testToNullableArray(): void
    {
        self::assertSame(null, NormalizerHelper::toNullableArray(null));
        self::assertSame(null, NormalizerHelper::toNullableArray([]));
        self::assertSame([0 => ' ', 5 => true], NormalizerHelper::toNullableArray([' ', null, '', '0', false, true]));
        self::assertEquals([new \stdClass()], NormalizerHelper::toNullableArray([new \stdClass()]));
        self::assertSame(['<br />', 77], NormalizerHelper::toNullableArray(['<br />', 77, false]));
    }

    public function testToNullableFloat(): void
    {
        self::assertSame(null, NormalizerHelper::toNullableFloat(null));
        self::assertSame(null, NormalizerHelper::toNullableFloat(0));
        self::assertSame(1.2, NormalizerHelper::toNullableFloat(1.2));
        self::assertSame(-99.3, NormalizerHelper::toNullableFloat(-99.3));
    }

    public function testToNullableInt(): void
    {
        self::assertSame(null, NormalizerHelper::toNullableInt(null));
        self::assertSame(null, NormalizerHelper::toNullableInt(0));
        self::assertSame(1, NormalizerHelper::toNullableInt(1));
        self::assertSame(-99, NormalizerHelper::toNullableInt(-99));
    }
}
