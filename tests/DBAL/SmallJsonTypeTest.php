<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Vrok\DoctrineAddons\Tests\DBAL;

use Doctrine\DBAL\Platforms\MySQL80Platform;
use Doctrine\DBAL\Types\ConversionException;
use PHPUnit\Framework\TestCase;
use Vrok\DoctrineAddons\DBAL\Types\SmallJsonType;

class SmallJsonTypeTest extends TestCase
{
    protected MySQL80Platform $platform;

    protected function setUp(): void
    {
        $this->platform = new MySQL80Platform();
    }

    public function testConvertToDatabaseValueAllowsNull(): void
    {
        $type = new SmallJsonType();
        $result = $type->convertToDatabaseValue(null, $this->platform);
        self::assertNull($result);
    }

    public function testConvertToDatabaseValueRequiresConvertibleValue(): void
    {
        $type = new SmallJsonType();

        $this->expectException(ConversionException::class);
        $type->convertToDatabaseValue(\NAN, $this->platform);
    }

    public function testConvertToDatabaseValueReturnsString(): void
    {
        $type = new SmallJsonType();
        $result = $type->convertToDatabaseValue(['key' => 'index'], $this->platform);
        self::assertSame('{"key":"index"}', $result);
    }

    public function testConvertToPhpValue(): void
    {
        $type = new SmallJsonType();
        $result = $type->convertToPHPValue('{"key":"index"}', $this->platform);

        self::assertIsArray($result);
        self::assertArrayHasKey('key', $result);
        self::assertSame('index', $result['key']);
    }

    public function testConvertToPhpValueAllowsNull(): void
    {
        $type = new SmallJsonType();
        $result = $type->convertToPHPValue(null, $this->platform);
        self::assertNull($result);
    }

    public function testConvertToPhpValueRequiresValidJson(): void
    {
        $type = new SmallJsonType();
        $this->expectException(ConversionException::class);
        $type->convertToPHPValue('{not: valid}', $this->platform);
    }
}
