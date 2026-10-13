# Upgrade to 4.0

* Update doctrine/dbal to version 4.5 or newer.
* `SmallJsonType` is removed. Map the columns with the `json` type instead, remove the
  `small_json` entry from `doctrine.dbal.types` and generate a migration for the changed column
  type. The stored JSON stays valid. On MySQL/MariaDB the column can no longer be part of a
  regular index.
* The test drivers and platforms are removed: `MariadbTestDriver`, `MariadbTestPlatform`,
  `PostgreSQLTestDriver` and `PostgreSQLTestPlatform`. Remove the `driver_class` from the
  `doctrine.dbal` config and use the default driver. The `RefreshDatabaseTrait` of
  vrok/symfony-addons 3.5 or newer disables the foreign key checks for the purge and resets the
  identities, so the fixtures get the same IDs in every test.
* `NormalizerHelper` is removed. Use the `Length` normalizer `trim` for plain text fields or
  copy the methods you need, e.g. `stripHtml()` and `toColor()`, into your project.
