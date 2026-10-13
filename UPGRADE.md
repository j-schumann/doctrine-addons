# Upgrade to 4.0

* `SmallJsonType` is removed. Map the columns with the `json` type instead, remove the
  `small_json` entry from `doctrine.dbal.types` and generate a migration for the changed column
  type. The stored JSON stays valid. On MySQL/MariaDB the column can no longer be part of a
  regular index.
