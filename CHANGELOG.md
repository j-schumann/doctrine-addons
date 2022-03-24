# Changelog
All notable changes to this project will be documented in this file.

This project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
