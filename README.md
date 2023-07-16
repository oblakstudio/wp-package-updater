<div align="center">

<h1 align="center" style="border-bottom: none;">📦 WordPress package updater</h1>
<h3 align="center">Simplifies the process of updating WordPress packages from custom repositories.</h3>

![Packagist Version](https://img.shields.io/packagist/v/oblak/wp-package-updater)
![Packagist PHP Version](https://img.shields.io/packagist/dependency-v/oblak/wp-package-updater/php)
[![semantic-release: angular](https://img.shields.io/badge/semantic--release-angular-e10079?logo=semantic-release)](https://github.com/semantic-release/semantic-release)

![Code Climate maintainability](https://img.shields.io/codeclimate/maintainability/oblakstudio/wp-package-updater)
[![Release](https://github.com/oblakstudio/wp-package-updater/actions/workflows/release.yml/badge.svg)](https://github.com/oblakstudio/wp-package-updater/actions/workflows/release.yml)

![GitHub](https://img.shields.io/github/license/oblakstudio/wp-package-updater)
![Packagist Downloads](https://img.shields.io/packagist/dm/oblak/wp-package-updater)

</div>

## Highlights
 * Standardizes the process of updating plugins / themes from custom repositories.
 * Fully integrates with Plugin / theme info API.
 * Easily extendable / customizable

## Installation

We officially support installing via composer only

### Via composer
```bash
composer require oblak/wp-package-updater
```

## Basic Usage

```Plugin_Updater``` and ```Theme_Updater``` are the main **abstract** classes of the package, and they need to be extended in order to create an updater class.
The class is responsible for registering the plugin / theme update hooks, and for updating the plugin / theme.

At minimum you need to implement the ``get_update_url()`` method, which is responsible for returning the update info URL.

### 1. Define your updater class

```php
<?php
namespace Vendor\My_Plugin;

use Oblak\WP\Plugin_Updater;
use Oblak\WP\Theme_Updater;



class My_Plugin_Updater extends Plugin_Updater {

    protected function get_update_url() {
        return 'https://my-plugin.com/api/update';
    }

}

class My_Theme_Updater extends Theme_Updater {

    protected function get_update_url() {
        return 'https://my-theme.com/api/update';
    }

}
```

### 2. Include the autoload file
```php
require_once __DIR__ . 'vendor/autoload.php';
```

### 3. Instantiate the updater class
```php
<?php

use Vendor\My_Plugin\My_Plugin_Updater;
use Vendor\My_Plugin\My_Theme_Updater;

new My_Plugin_Updater('plugin-slug');
new My_Theme_Updater('theme-slug');
```

## Advanced Usage

Depending on your needs, you can override several methods in the updater class to customize the update process according to your repository API.

Some of the functions you can customize:

* ``get_headers`` - Returns the headers for the update request.
* ``send_request`` - Sends the update request to the repository API.
* ``validate_response`` - Validates the response from the repository API.
* ``get_transient_prefix`` - Returns the transient prefix for the plugin / theme update information.

## Contributing

Contributions are welcome from everyone. We have [contributing guidelines](CONTRIBUTING.md) to help you get started.

## Credits and special thanks

This project is maintained by [Oblak Studio](https://oblak.studio).

## License

This project is licensed under the [GNU General Public License v2.0](LICENSE).


