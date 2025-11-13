# LCMS Storage - PHP SDK

Official PHP SDK for LCMS Storage - Asset management and CDN platform.

## Features

- Upload files to LCMS Storage with automatic S3 integration
- Delete assets from storage
- List directory contents
- Generate asset URLs with automatic CDN delivery
- Transform images on-the-fly with URL parameters (resize, format conversion)
- Type-safe with PHP 8.1+ features
- Minimal dependencies (Guzzle HTTP client only)

## Requirements

- PHP 8.1 or higher
- Composer

## Installation

```bash
composer require appitudeio/lcms-storage
```

## Quick Start

```php
<?php

require_once 'vendor/autoload.php';

use LCMS\Storage\Storage;

// Initialize client
$storage = new Storage('your-domain.com', 'your_api_key');

// Upload a file
$response = $storage->upload('/path/to/local/image.jpg', '/uploads/image.jpg');
echo "Uploaded to: " . $response->url['asset'];

// List directory contents
$listing = $storage->list('/uploads');
foreach ($listing->items as $item) {
    echo $item['key'] . " - " . $item['url'] . "\n";
}

// Delete file
$storage->delete('/uploads/old-image.jpg');
```

## API Reference

### Constructor

```php
$storage = new LCMS\Storage\Storage(string $domain, string $apiKey);
```

Initialize the LCMS Storage client with your domain and API key.

---

### upload()

```php
upload(string $filePath, string $remotePath): UploadResponse
```

Upload a file to LCMS Storage.

**Example:**
```php
$response = $storage->upload('/tmp/photo.jpg', '/gallery/photo.jpg');
if ($response->success) {
    echo "Uploaded: " . $response->url['asset'];
}
```

---

### delete()

```php
delete(string $path): void
```

Delete a file from LCMS Storage.

**Example:**
```php
$storage->delete('/gallery/old-photo.jpg');
```

---

### list()

```php
list(string $path = ''): ListResponse
```

List contents of a directory.

**Example:**
```php
$listing = $storage->list('/gallery');

foreach ($listing->items as $item) {
    echo $item['key'] . " - " . $item['size'] . " bytes\n";
}

foreach ($listing->prefixes as $prefix) {
    echo "Subdirectory: " . $prefix . "\n";
}
```

---

## Response Objects

### UploadResponse

**Properties:**
- `bool $success` - Whether upload was successful
- `string $message` - Success or error message
- `array $url` - Contains 'asset' and 'upload' URLs

**Methods:**
- `toArray(): array`
- `toJson(int $options = 0): string`

### ListResponse

**Properties:**
- `bool $success` - Whether the operation was successful
- `string $message` - Success or error message
- `string $path` - Directory path that was listed
- `string $assetBaseUrl` - Base URL for accessing assets
- `array $items` - Array of file items with 'key', 'size', 'url', etc.
- `array $prefixes` - Array of subdirectory prefixes

**Methods:**
- `count(): int` - Number of items
- `isEmpty(): bool` - Whether directory is empty
- `toArray(): array`
- `toJson(int $options = 0): string`

---

## Error Handling

```php
try {
    $response = $storage->upload('/path/to/file.jpg', '/uploads/file.jpg');
    if ($response->success) {
        echo "Success: " . $response->url['asset'];
    } else {
        echo "Upload failed: " . $response->message;
    }
} catch (\InvalidArgumentException $e) {
    echo "Validation error: " . $e->getMessage();
} catch (\RuntimeException $e) {
    echo "Upload failed: " . $e->getMessage();
}
```

---

## License

MIT License - see [LICENSE](./LICENSE) file for details.

---

## Support

For issues and questions:
- GitHub Issues: [lcms-storage-php/issues](https://github.com/appitudeio/lcms-storage-php/issues)
- Email: support@appitudeio.com

---

## Related

- [LCMS Storage TypeScript SDK](https://github.com/appitudeio/lcms-storage-node)
- [LCMS Documentation](https://docs.logicalcms.com)
