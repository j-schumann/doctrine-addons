# Changelog
All notable changes to this project will be documented in this file.

This project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [3.0.0] - 2022-03-24
### Added
- `ImportExport\Helper` - a class (together with corresponding PHP attributes) that allows
  converting entities from/to arrays
### Updated:
- *breaking*: requires PHP ^8.1, so the code can use all typehints and other new features

## [2.3.0] - 2022-03-19
### Added
- `UmlautTransliterator` to correctly translate umlauts in slugs
### Updated
- `NormalizerHelper` now supports normalization of HTML color values (prepend #)
  and locales (@see `Locale::canonicalize`)
