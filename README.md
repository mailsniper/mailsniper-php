# MailSniper PHP SDK

[![PHP Version](https://img.shields.io/badge/php-%5E8.1-blue)](https://php.net)
[![PSR-18](https://img.shields.io/badge/PSR--18-HTTP%20Client-green)](https://www.php-fig.org/psr/psr-18/)
[![PSR-12](https://img.shields.io/badge/PSR--12-Coding%20Style-green)](https://www.php-fig.org/psr/psr-12/)

---
**Get 10,000 Free API Calls** - Stop fake signups and disposable emails before they hurt your business. [Create your free account →](https://mailsniperapp.com?utm_source=github&utm_medium=readme)

---

Official PHP SDK for the [MailSniper Email Verification API](https://mailsniperapp.com?utm_source=github&utm_medium=readme). Verify email addresses, detect disposable emails, check domain reputation, and manage your API usage with a clean, type-safe interface.

## Features

- ✅ **PSR-18 HTTP Client** - Use any HTTP client you prefer (Guzzle included by default)
- ✅ **PSR-12 Compliant** - Follows modern PHP coding standards
- ✅ **Flexible** - Easy to extend and customize
- ✅ **Email Verification** - Validate email addresses with detailed analysis
- ✅ **Quota Management** - Track and monitor API usage
- ✅ **Error Handling** - Specific exceptions for different error types

## Requirements

- PHP 8.1 or higher
- A PSR-18 HTTP client (Guzzle recommended and included in dev dependencies)

## Installation

Install via Composer:

```bash
composer require mailsniper/mailsniper-php
```

For the recommended HTTP client (Guzzle):

```bash
composer require guzzlehttp/guzzle
composer require php-http/discovery
```

## Getting Your API Key

To use this SDK, you'll need an API key. [Create a free account at mailsniperapp.com](https://mailsniperapp.com?utm_source=github&utm_medium=readme) to get your API token with 10,000 free API calls included.

## Quick Start

### Basic Usage with Auto-Discovery

The simplest way to get started is using the `create()` factory method, which auto-discovers available HTTP clients:

```php
<?php

require 'vendor/autoload.php';

use MailSniper\Client;

// Create client with auto-discovery (requires php-http/discovery)
$client = Client::create('your_api_key_here');

// Verify an email address
$result = $client->verifyEmail('user@example.com');

if ($result->isValid()) {
    echo "Email is valid!\n";
    echo "Is disposable: " . ($result->isDisposable() ? 'Yes' : 'No') . "\n";
    echo "Is public provider (Gmail, Yahoo, etc.): " . ($result->isPublicProvider() ? 'Yes' : 'No') . "\n";
    echo "Risk score: {$result->getRisk()}\n";
} else {
    echo "Email is invalid\n";
}

// Check your API usage
$usage = $client->getUsage();
echo "Used {$usage->getUsed()} of {$usage->getTotal()} requests\n";
echo "Remaining: {$usage->getRemaining()}\n";

if ($usage->isApproachingLimit()) {
    echo "Warning: You're approaching your quota limit!\n";
}
```

### Manual HTTP Client Configuration

For more control, you can provide your own PSR-18 HTTP client:

```php
<?php

use MailSniper\Client;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\HttpFactory;

// Create HTTP client and factories
$httpClient = new GuzzleClient([
    'timeout' => 30,
    'connect_timeout' => 5,
]);

$httpFactory = new HttpFactory();

// Create MailSniper client
$client = new Client(
    apiKey: 'your_api_key_here',
    httpClient: $httpClient,
    requestFactory: $httpFactory
);

$result = $client->verifyEmail('test@example.com');
```

## Email Verification

### Detailed Example

```php
<?php

use MailSniper\Client;
use MailSniper\Exception\MailSniperException;

$client = Client::create('your_api_key_here');

try {
    $result = $client->verifyEmail('user@example.com');

    // Basic information
    echo "Email: {$result->getEmail()}\n";
    echo "User: {$result->getUser()}\n";
    echo "Domain: {$result->getDomain()}\n";

    // Validation results
    echo "Valid: " . ($result->isValid() ? 'Yes' : 'No') . "\n";
    echo "Disposable: " . ($result->isDisposable() ? 'Yes' : 'No') . "\n";
    echo "Public Provider: " . ($result->isPublicProvider() ? 'Yes' : 'No') . "\n";
    echo "University: " . ($result->isUniversity() ? 'Yes' : 'No') . "\n";
    echo "Spam: " . ($result->isSpam() ? 'Yes' : 'No') . "\n";

    // Risk assessment
    echo "Risk Score: {$result->getRisk()} (0-100)\n";

    // DNS information
    $dns = $result->getDns();
    echo "MX Servers: " . implode(', ', $dns->getMxServers()) . "\n";
    echo "Has A DNS Record: " . ($dns->hasARootRecord() ? 'Yes' : 'No') . "\n";

    // Quota information (if available)
    if ($quota = $result->getQuota()) {
        echo "Requests Remaining: {$quota->getRemaining()}\n";
    }

} catch (MailSniperException $e) {
    echo "Error: {$e->getMessage()}\n";
}
```

### Risk Score Interpretation

- **0-20**: Low risk - Generally safe email addresses
- **21-50**: Medium risk - May require additional verification
- **51-80**: High risk - Proceed with caution
- **81-100**: Very high risk - Disposable, spam, or invalid domains

## Usage Information

Track your API quota consumption:

```php
<?php

use MailSniper\Client;

$client = Client::create('your_api_key_here');

$usage = $client->getUsage();

echo "Total Quota: {$usage->getTotal()}\n";
echo "Used: {$usage->getUsed()}\n";
echo "Remaining: {$usage->getRemaining()}\n";
echo "Percentage Used: {$usage->getPercentageUsed()}%\n";

if ($usage->isApproachingLimit()) {
    // Alert when >= 80% quota is used
    echo "⚠️  Warning: Approaching quota limit!\n";
}
```

## Error Handling

The SDK provides specific exception types for different error scenarios:

```php
<?php

use MailSniper\Client;
use MailSniper\Exception\AuthenticationException;
use MailSniper\Exception\ValidationException;
use MailSniper\Exception\QuotaExceededException;
use MailSniper\Exception\ServerException;
use MailSniper\Exception\MailSniperException;

$client = Client::create('your_api_key_here');

try {
    $result = $client->verifyEmail('test@example.com');

} catch (AuthenticationException $e) {
    // 401 - Invalid or missing API key
    echo "Authentication failed: {$e->getMessage()}\n";
    echo "Error code: {$e->getErrorCode()}\n";

} catch (ValidationException $e) {
    // 400 - Invalid email format or parameters
    echo "Validation error: {$e->getMessage()}\n";

} catch (QuotaExceededException $e) {
    // 429 - API quota exceeded
    echo "Quota exceeded: {$e->getMessage()}\n";
    echo "Please upgrade your plan or purchase additional requests.\n";

} catch (ServerException $e) {
    // 5xx - Server errors
    echo "Server error: {$e->getMessage()}\n";
    echo "Status code: {$e->getStatusCode()}\n";

} catch (MailSniperException $e) {
    // Other errors
    echo "Error: {$e->getMessage()}\n";
}
```

## API Reference

### Client

#### Constructor

```php
public function __construct(
    string $apiKey,
    ClientInterface $httpClient,
    RequestFactoryInterface $requestFactory,
    string $baseUrl = 'https://api.mailsniperapp.com'
)
```

#### Factory Method

```php
public static function create(
    string $apiKey,
    string $baseUrl = 'https://api.mailsniperapp.com'
): self
```

## Development

### Running Tests

```bash
composer test
```

### Code Quality

Run PHPStan (Level 7):

```bash
composer phpstan
```

Run PHP-CS-Fixer (PSR-12):

```bash
composer cs-fix
```

Check code style without fixing:

```bash
composer cs-check
```

Run all quality checks:

```bash
composer quality
```

## Configuration

### Custom Base URL

For testing or development environments:

```php
$client = Client::create(
    'your_api_key_here',
    'http://api.localhost'  // Custom base URL
);
```

### Using a Different HTTP Client

Any PSR-18 compliant HTTP client can be used:

```php
use MailSniper\Client;
use Symfony\Component\HttpClient\Psr18Client;
use Nyholm\Psr7\Factory\Psr17Factory;

$psr17Factory = new Psr17Factory();
$httpClient = new Psr18Client();

$client = new Client(
    'your_api_key_here',
    $httpClient,
    $psr17Factory  // Request factory
);
```

## Rate Limits & Quotas

MailSniper uses a **fixed quota system** rather than time-based rate limiting:

- Each successful API call consumes 1 request from your quota
- Failed requests (4xx, 5xx errors) do **not** consume quota
- Authentication failures do **not** consume quota
- Default quota for new signups: 10,000 requests
- Monitor quota using the `getUsage()` method or response headers

## Support

- **Documentation**: https://docs.mailsniperapp.com?utm_source=github&utm_medium=readme
- **Email**: hello@mailsniperapp.com
- **Issues**: https://github.com/mailsniper/mailsniper-php/issues

## License

This SDK is licensed under the MIT License. See the LICENSE file for details.

## Contributing

Contributions are welcome! Please:

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new functionality
5. Run quality checks (`composer quality`)
6. Submit a pull request

## Changelog

### 1.0.0 (Initial Release)

- Email verification endpoint
- Usage information endpoint
- PSR-18 HTTP client support
- Comprehensive error handling
