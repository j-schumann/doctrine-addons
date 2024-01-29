<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Vrok\DoctrineAddons\Tests\DBAL;

use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Types\ConversionException;
use PHPUnit\Framework\TestCase;
use Vrok\DoctrineAddons\DBAL\Types\SmallJsonType;

class SmallJsonTypeTest extends TestCase
{
    protected MySqlPlatform $platform;

    public function setUp(): void
    {
        $this->platform = new MySqlPlatform();
    }

    public function testConvertToDatabaseValueAllowsNull(): void
    {
        $type = new SmallJsonType();
        $result = $type->convertToDatabaseValue(null, $this->platform);
        $this->assertNull($result);
    }

    public function testConvertToDatabaseValueRequiresConvertibleValue(): void
    {
        $type = new SmallJsonType();

        $this->expectException(ConversionException::class);
        $type->convertToDatabaseValue(NAN, $this->platform);
    }

    public function testConvertToDatabaseValueReturnsString(): void
    {
        $type = new SmallJsonType();
        $result = $type->convertToDatabaseValue(['key' => 'index'], $this->platform);
        $this->assertSame('{"key":"index"}', $result);
    }

    public function testConvertToPHPValue(): void
    {
        $type = new SmallJsonType();
        $result = $type->convertToPHPValue('{"key":"index"}', $this->platform);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('key', $result);
        $this->assertSame('index', $result['key']);
    }

    public function testConvertToPHPValueAllowsNull(): void
    {
        $type = new SmallJsonType();
        $result = $type->convertToPHPValue(null, $this->platform);
        $this->assertNull($result);
    }

    public function testConvertToPHPValueRequiresValidJson(): void
    {
        $type = new SmallJsonType();
        $this->expectException(ConversionException::class);
        $type->convertToPHPValue('{not: valid}', $this->platform);
    }
}
