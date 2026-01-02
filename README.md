# Filesystem storage adapter for Imbo

Filesystem storage adapter for Imbo.

## Installation

    composer require imbo/imbo-filesystem-adapters

## Usage

```php
$mainStorageAdapter = new Imbo\Storage\Filesystem($path);
$imageVariationsStorageAdapter = new Imbo\EventListener\ImageVariations\Storage\Filesystem($path);
```

## License

MIT, see [LICENSE](LICENSE).
