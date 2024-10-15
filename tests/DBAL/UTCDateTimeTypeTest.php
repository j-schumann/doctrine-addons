<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Vrok\DoctrineAddons\Tests\DBAL;

use Doctrine\DBAL\Platforms\MySQL80Platform;
use Doctrine\DBAL\Types\ConversionException;
use PHPUnit\Framework\TestCase;
use Vrok\DoctrineAddons\DBAL\Types\UTCDateTimeType;

class UTCDateTimeTypeTest extends TestCase
{
    protected MySQL80Platform $platform;

    public function setUp(): void
    {
        $this->platform = new MySQL80Platform();
    }

    public function testConvertToDatabaseValueAllowsNull(): void
    {
        $type = new UTCDateTimeType();
        $result = $type->convertToDatabaseValue(null, $this->platform);
        self::assertNull($result);
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
        self::assertSame('2019-12-31 23:00:00', $result);
    }

    public function testConvertToPhpValue(): void
    {
        $type = new UTCDateTimeType();
        $result = $type->convertToPHPValue('2019-01-01 00:00:00', $this->platform);

        self::assertInstanceOf(\DateTimeImmutable::class, $result);
        self::assertSame('2019-01-01 00:00:00', $result->format('Y-m-d H:i:s'));
        self::assertSame('UTC', $result->getTimezone()->getName());
    }

    public function testConvertToPhpValueAllowsNull(): void
    {
        $type = new UTCDateTimeType();
        $result = $type->convertToPHPValue(null, $this->platform);
        self::assertNull($result);
    }

    public function testConvertToPhpValueRequiresString(): void
    {
        $type = new UTCDateTimeType();

        $this->expectException(ConversionException::class);
        $type->convertToPHPValue(new \stdClass(), $this->platform);
    }

    public function testConvertToPhpValueRequiresValidDateTime(): void
    {
        $type = new UTCDateTimeType();

        $this->expectException(ConversionException::class);
        $type->convertToPHPValue('not valid', $this->platform);
    }
}
