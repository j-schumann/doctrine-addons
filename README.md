# vrok/doctrine-addons

This is a library with additional classes for usage in combination with the
Doctrine ORM.  

[![CI Status](https://github.com/j-schumann/doctrine-addons/actions/workflows/ci.yaml/badge.svg)](https://github.com/j-schumann/doctrine-addons/actions)
[![Coverage Status](https://coveralls.io/repos/github/j-schumann/doctrine-addons/badge.svg?branch=main)](https://coveralls.io/github/j-schumann/doctrine-addons?branch=main)

## Enable Postgres specific DQL functions
`CAST` implements the corresponding function from Postgres to convert types:
```php
$queryBuilder->expr()->like('CAST(u.varchar, \'text\'))', ':parameterName')
```

`JSON_CONTAINS_TEXT` allows to use the [postgres-only "?" operator](https://www.postgresql.org/docs/9.5/functions-json.html#FUNCTIONS-JSONB-OP-TABLE)
to search for strings within jsonb fields.
This for example allows to filter Symfony users correctly by role,
e.g. if you use role names that are part of others (ROLE_SUPER & ROLE_SUPER_ADMIN) where
using `LIKE` would fail.
```php
$qb->andWhere("JSON_CONTAINS_TEXT(u.roles, :searchRole) = true")
   ->setParameter('searchRole', 'ROLE_ADMIN');
```

`JSON_FIELD_AS_TEXT` allows to use the [postgres-only "->>" operator](https://www.postgresql.org/docs/9.5/functions-json.html#FUNCTIONS-JSON)
to get JSON data within a jsonb fields as string.
This for example allows to search for records that embed JSON,
e.g. if you store metadata or addresses:
```php
$qb->andWhere("JSON_FIELD_AS_TEXT('u.address, :addrField) = :addrValue")
    ->setParameter('addrField', 'city')
    ->setParameter('addrValue', 'Dresden');
```

Add to config/packages/doctrine.yaml:
```yaml
doctrine:
    orm:
        dql:
            string_functions:
                CAST: Vrok\DoctrineAddons\ORM\Query\AST\CastFunction
                JSON_CONTAINS_TEXT: Vrok\DoctrineAddons\ORM\Query\AST\JsonContainsTextFunction
                JSON_FIELD_AS_TEXT: Vrok\DoctrineAddons\ORM\Query\AST\JsonFieldAsTextFunction
```

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

## Enable the PostgreSQLTestDriver in Symfony

config/packages/test/doctrine.yaml
```yaml
doctrine:
    dbal:
        # Default purge/TRUNCATE behavior of Postgres does not reset autoincrement
        # values, so we use our custom driver to reset identities for TRUNCATE.
        # "driver" left blank intentionally
        driver:
        driver_class: Vrok\DoctrineAddons\DAL\Driver\PostgreSQLTestDriver
```

## Lockable entities

Implementing a simple `LockableInterface` allows unified handling of different
entities that can be locked/unlocked by admins etc. The provided interface
makes very little assumptions about your code, except that each entity can
be locked/unlocked at any time (regardless of other states the entity might have).

The `LockableTrait` provides an opinionated implementation of that interface,
by specifying a boolean property `locked` that defaults to false and that has
a Symfony group attribute of `default:read`. 

## Slugs with correct umlauts in Symfony

Add this to your services.yaml to have ae, ue, oe in your slugs instead of
a, u, o for ä, ü, ö.  
This also handles some other chars, e.g. accents.
```yaml
    gedmo.listener.sluggable:
        class: Gedmo\Sluggable\SluggableListener
        tags:
            - { name: doctrine.event_subscriber, connection: default }
        calls:
            - [ setAnnotationReader, [ "@annotation_reader" ] ] # only if you still use annotations
            - [ setTransliterator, [ [ 'Vrok\DoctrineAddons\Util\UmlautTransliterator', 'transliterate' ] ] ]
```

## Import / export entities from / to arrays

Short Example, for more details see [ExportEntity](tests/Fixtures/ExportEntity.php) 
/ [ImportEntity](tests/Fixtures/ImportEntity.php) and [ExportTest](tests/ImportExport/ExportTest.php)
/ [ImportTest](tests/ImportExport/ImportTest.php) for all features.
Allows to export referenced entities (or only their identifiers) and collections.

```php
use Vrok\DoctrineAddons\ImportExport\ExportableEntity;
use Vrok\DoctrineAddons\ImportExport\ExportableProperty;
use Vrok\DoctrineAddons\ImportExport\Helper;
use Vrok\DoctrineAddons\ImportExport\ImportableEntity;
use Vrok\DoctrineAddons\ImportExport\ImportableProperty;

#[ExportableEntity]
#[ImportableEntity]
class Entity
{
    #[ExportableProperty]
    #[ImportableProperty]
    public int $id = 0;

    #[ExportableProperty]
    #[ImportableProperty]
    public ?DateTimeImmutable $timestamp = null;
}

$entity = new Entity();
$entity->id = 1;
$entity->timestamp = new Datetime();

$helper = new Helper();
$export = $helper->toArray($entity);

// $export === [
//     'id'        => 1,
//     'timestamp' => '2022-03-23....',
// ]

$newInstance = $helper->fromArray($export, Entity::class);
```