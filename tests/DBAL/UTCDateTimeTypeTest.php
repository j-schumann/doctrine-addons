<?php

/** @noinspection PhpUnhandledExceptionInspection */

namespace Vrok\DoctrineAddons\Tests\DBAL;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Vrok\DoctrineAddons\DBAL\Types\UTCDateTimeType;

final class UTCDateTimeTypeTest extends AbstractDbalTestCase
{
    private MySQLPlatform $platform;

    protected function setUp(): void
    {
        $this->platform = new MySQLPlatform();
    }

    public function testConvertToDatabaseValueWithNull(): void
    {
        $type = new UTCDateTimeType();
        $result = $type->convertToDatabaseValue(null, $this->platform);
        self::assertNull($result);
    }

    public function testConvertToDatabaseValueFailsWithObject(): void
    {
        $type = new UTCDateTimeType();

        $this->expectException(ConversionException::class);
        $type->convertToDatabaseValue(new \stdClass(), $this->platform);
    }

    public function testConvertToDatabaseValue(): void
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

    public function testConvertToPhpValueWithNull(): void
    {
        $type = new UTCDateTimeType();
        $result = $type->convertToPHPValue(null, $this->platform);
        self::assertNull($result);
    }

    public function testConvertToPhpValueFailsWithObject(): void
    {
        $type = new UTCDateTimeType();

        $this->expectException(ConversionException::class);
        $type->convertToPHPValue(new \stdClass(), $this->platform);
    }

    public function testConvertToPhpValueFailsWithInvalidString(): void
    {
        $type = new UTCDateTimeType();

        $this->expectException(ConversionException::class);
        $type->convertToPHPValue('not valid', $this->platform);
    }

    public function testConvertWithDatabase(): void
    {
        $local = new \DateTimeImmutable('2026-10-09 18:30:15', new \DateTimeZone('Europe/Berlin'));

        [$raw, $loaded] = self::storeAndLoad(new UTCDateTimeType(), $local);

        self::assertStringStartsWith('2026-10-09 16:30:15', $raw);
        self::assertSame('UTC', $loaded->getTimezone()->getName());
        self::assertSame($local->getTimestamp(), $loaded->getTimestamp());
    }
}
