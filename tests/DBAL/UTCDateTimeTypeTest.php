<?php

declare(strict_types=1);

namespace Vrok\DoctrineAddons\DBAL\Tests;

use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Types\ConversionException;
use PHPUnit\Framework\TestCase;
use Vrok\DoctrineAddons\DBAL\Types\UTCDateTimeType;

class UTCDateTimeTypeTest extends TestCase
{
    protected MySqlPlatform $platform;

    public function setUp(): void
    {
        $this->platform = new MySqlPlatform();
    }

    public function testConvertToDatabaseValueAllowsNull()
    {
        $type = new UTCDateTimeType();
        $result = $type->convertToDatabaseValue(null, $this->platform);
        $this->assertNull($result);
    }

    public function testConvertToDatabaseValueRequiresDateTime()
    {
        $type = new UTCDateTimeType();

        $this->expectException(ConversionException::class);
        $result = $type->convertToDatabaseValue(new \stdClass(), $this->platform);
    }

    public function testConvertToDatabaseValueReturnsString()
    {
        $tz = new \DateTimeZone('Europe/Berlin'); // GMT+1
        $type = new UTCDateTimeType();
        $date = new \DateTime('2020-01-01', $tz);
        $result = $type->convertToDatabaseValue($date, $this->platform);
        $this->assertSame('2019-12-31 23:00:00', $result);
    }

    public function testConvertToPHPValue()
    {
        $type = new UTCDateTimeType();
        $result = $type->convertToPHPValue('2019-01-01 00:00:00', $this->platform);

        $this->assertInstanceOf(\DateTimeImmutable::class, $result);
        $this->assertSame('2019-01-01 00:00:00', $result->format('Y-m-d H:i:s'));
        $this->assertSame('UTC', $result->getTimezone()->getName());
    }

    public function testConvertToPHPValueAllowsNull()
    {
        $type = new UTCDateTimeType();
        $result = $type->convertToPHPValue(null, $this->platform);
        $this->assertNull($result);
    }

    public function testConvertToPHPValueRequiresString()
    {
        $type = new UTCDateTimeType();

        $this->expectException(ConversionException::class);
        $result = $type->convertToPHPValue(new \stdClass(), $this->platform);
    }

    public function testConvertToPHPValueRequiresValidDateTime()
    {
        $type = new UTCDateTimeType();

        $this->expectException(ConversionException::class);
        $result = $type->convertToPHPValue('not valid', $this->platform);
    }
}
