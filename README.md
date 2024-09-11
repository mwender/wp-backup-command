# WP Backup Command

This WP-CLI package allows you to back up plugins or themes by creating a zip or tar.gz archive.

## Installation

`wp package install mwender/wp-backup-command`

## Usage

To back up a plugin:

`wp plugin backup <plugin-slug>`

To back up a theme:

`wp theme backup <theme-slug>`

## Changelog

_1.1.1_

- Updating composer.json `version` to force recognition of new release.

_1.1.0_

- Backup respective plugin/theme from the parent directory.
- Return to the original directory after execution.
- Move the backup file to the original directory.

_1.0.0_

- Initial release.

## License

This package is licensed under the MIT license.