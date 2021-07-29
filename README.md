# vrok/doctrine-addons

This is a library with additional classes for usage in combination with the
Doctrine ORM.  

[![CI Status](https://github.com/j-schumann/doctrine-addons/workflows/ci.yaml/badge.svg)](https://github.com/j-schumann/doctrine-addons/actions)
[![Coverage Status](https://coveralls.io/repos/github/j-schumann/doctrine-addons/badge.svg?branch=master)](https://coveralls.io/github/j-schumann/doctrine-addons?branch=master)

## Enable types in Symfony

config/packages/doctrine.yaml
```yaml
doctrine:
    dbal:
        types:
            # force all dates/times to be stored in UTC
            utcdatetime:
                name: datetime_immutable
                class: Vrok\DoctrineAddons\DBAL\Types\UTCDateTimeType

            # MariaDB does not support the JSON type, so we do not benefit from
            # validation/searching/path syntax etc. Also it uses a LONGTEXT
            # instead, which has a performance hit because it is stored outside
            # the row and causes possible temp tables to be written to disk
            smalljson:
                name: small_json
                class: Vrok\DoctrineAddons\DBAL\Types\SmallJsonType
```

## Enable the MariadbTestDriver in Symfony

config/packages/test/doctrine.yaml
```yaml
doctrine:
    dbal:
        # There is a bug in Doctrine\Common\DataFixtures\Purger\ORMPurger
        # which causes tables that are target of a foreign key constraint to
        # be deleted before the association table(s), which in turn causes
        # "1701 Cannot truncate a table referenced in a foreign key constraint"
        # So we use our custom driver to disable foreign key checks for TRUNCATE
        # because only with TRUNCATE instead of DELETE FROM we ensure the same
        # autoincrement IDs for fixtures in tests
        # "driver" left blank intentionally
        driver:
        driver_class: Vrok\DoctrineAddons\DAL\Driver\MariadbTestDriver
```