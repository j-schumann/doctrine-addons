# Changelog
All notable changes to this project will be documented in this file.

This project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.15.0] - 2025-06-28
### Updated
- removed support for PHP 8.2

### Fixed
- Updated `UmlautTransliterator` to handle superscript/subscript numbers, as the
  Behat\Transliterator previously did.
- Removed the unused dependency to Behat\Transliterator


## [2.14.0] - 2025-06-08
### Updated
- Updated `UmlautTransliterator` to work without the abandoned
  Behat\Transliterator. This may cause BC breaks in case the UmlautTransliterator
  was used separately from Gedmo\DoctrineExtensions and also when Cyrillic 
  words/letters are used.

### Deprecated
- `ImportExport\Helper` and it's corresponding PHP attributes will be removed in
  3.0, please use the standalone package `vrok\import-export` instead, it has 
  improved functionality & usability (less attributes to use).

## [2.13.0] - 2024-10-15
### Updated
- support for DBAL 4.0 
- removed support for DBAL 3.x

## [2.12.0] - 2024-09-25
### Updated
- support for ORM 3.0
- removed support for ORM 2.x
- removed support for PHP 8.1

## [2.11.0] - 2024-05-16
### Added
- `ORM\Query\AST\ContainsFunction` - to check if JSON / an array contains a
  given value

## [2.10.0] - 2024-05-13
### Updated
- ImportExport\Helper to allow nested (lists of) DTOs to be (de)serialized

## [2.9.1] - 2024-01-29
### Fixed
- deprecations & return typehints

## [2.9.0] - 2023-12-30
### Added
- Support for Symfony v7 (only relevant for dev)
- `LockableInterface` / `LockableTrait`

## [2.8.0] - 2023-06-04
### Added
- ImportExport\Helper allow string IDs for imports referencing existing entities

## [2.7.0] - 2023-04-21
### Added
- ImportExport\Helper can fetch related entities by ID when importing

### Fixed
- deprecation with `PostgreSQLTestPlatform`

## [2.6.2] - 2023-01-24
### Updated
- dependencies, mainly doctrine/lexer to ^2.1

## [2.6.1] - 2022-10-05
### Fixed
- `ImportExport\Helper` handling of union types

## [2.6.0] - 2022-08-11
### Added
- `ImportExport\Helper` can now import lists of objects (e.g. an array of DTOs), by
  using the new `listOf` property of the `ImportableProperty` attribute.

## [2.5.0] - 2022-07-22
### Added
- `ORM\Query\AST\JsonFieldAsTextFunction` - to return a selected field within embedded JSON
  as string or compare it to a value
### Updated:
- `ImportExport\Helper` to use static caches for im-/exportable classes & properties to
  improve performance

## [2.4.0] - 2022-03-24
### Added
- `ImportExport\Helper` - a class (together with corresponding PHP attributes) that allows
  converting entities from/to arrays
### Updated:
- require PHP ^8.1, so the code can use all typehints and other new features
  (this is not a breaking change according to
  [Semver](https://github.com/semver/semver/blob/df7bd79bda7d7fe6da20d0724fe0111678cbaa8f/semver.md#what-should-i-do-if-i-update-my-own-dependencies-without-changing-the-public-api))

## [2.3.0] - 2022-03-19
### Added
- `UmlautTransliterator` to correctly translate umlauts in slugs
### Updated
- `NormalizerHelper` now supports normalization of HTML color values (prepend #)
  and locales (@see `Locale::canonicalize`)
