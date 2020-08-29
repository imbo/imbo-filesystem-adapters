# Filesystem storage adapter for Imbo

[![CI](https://github.com/imbo/imbo-filesystem-adapters/workflows/CI/badge.svg)](https://github.com/imbo/imbo-filesystem-adapters/actions?query=workflow%3ACI)

Filesystem storage adapter for Imbo.

## Installation

    composer require imbo/imbo-filesystem-adapters

## Usage

Create the adapter by giving it a path:

```php
use Imbo\Storage\Filesystem;

$adapter = new Filesystem($path);
```

## License

MIT, see [LICENSE](LICENSE).