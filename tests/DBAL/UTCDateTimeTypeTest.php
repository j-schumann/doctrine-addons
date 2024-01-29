<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Vrok\DoctrineAddons\Tests\DBAL;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Types\ConversionException;
use PHPUnit\Framework\TestCase;
use Vrok\DoctrineAddons\DBAL\Types\UTCDateTimeType;

class UTCDateTimeTypeTest extends TestCase
{
    protected MySQLPlatform $platform;

    public function setUp(): void
    {
        $this->platform = new MySQLPlatform();
    }

    public function testConvertToDatabaseValueAllowsNull(): void
    {
        $type = new UTCDateTimeType();
        $result = $type->convertToDatabaseValue(null, $this->platform);
        $this->assertNull($result);
    }

    public function testConvertToDatabaseValueRequiresDateTime(): void
    {
        $type = new UTCDateTimeType();

        $this->expectException(ConversionException::class);
        $type->convertToDatabaseValue(new \stdClass(), $this->platform);
    }

    public function testConvertToDatabaseValueReturnsString(): void
    {
        $tz = new \DateTimeZone('Europe/Berlin'); // GMT+1
        $type = new UTCDateTimeType();
        $date = new \DateTime('2020-01-01', $tz);
        $result = $type->convertToDatabaseValue($date, $this->platform);
        $this->assertSame('2019-12-31 23:00:00', $result);
    }

    public function testConvertToPHPValue(): void
    {
        $type = new UTCDateTimeType();
        $result = $type->convertToPHPValue('2019-01-01 00:00:00', $this->platform);

        $this->assertInstanceOf(\DateTimeImmutable::class, $result);
        $this->assertSame('2019-01-01 00:00:00', $result->format('Y-m-d H:i:s'));
        $this->assertSame('UTC', $result->getTimezone()->getName());
    }

    public function testConvertToPHPValueAllowsNull(): void
    {
        $type = new UTCDateTimeType();
        $result = $type->convertToPHPValue(null, $this->platform);
        $this->assertNull($result);
    }

    public function testConvertToPHPValueRequiresString(): void
    {
        $type = new UTCDateTimeType();

        $this->expectException(ConversionException::class);
        $type->convertToPHPValue(new \stdClass(), $this->platform);
    }

    public function testConvertToPHPValueRequiresValidDateTime(): void
    {
        $type = new UTCDateTimeType();

        $this->expectException(ConversionException::class);
        $type->convertToPHPValue('not valid', $this->platform);
    }
}
