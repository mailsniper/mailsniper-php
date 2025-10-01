<?php

declare(strict_types=1);

namespace MailSniper\Tests\Unit\Exception;

use MailSniper\Exception\ApiException;
use MailSniper\Exception\AuthenticationException;
use MailSniper\Exception\MailSniperException;
use MailSniper\Exception\QuotaExceededException;
use MailSniper\Exception\ServerException;
use MailSniper\Exception\ValidationException;
use PHPUnit\Framework\TestCase;

class ExceptionTest extends TestCase
{
    public function testMailSniperException(): void
    {
        $exception = new MailSniperException('Test message', 123);

        $this->assertSame('Test message', $exception->getMessage());
        $this->assertSame(123, $exception->getCode());
    }

    public function testApiException(): void
    {
        $exception = new ApiException('test_error', 'Test error message', 400);

        $this->assertSame('[test_error] Test error message', $exception->getMessage());
        $this->assertSame('test_error', $exception->getErrorCode());
        $this->assertSame(400, $exception->getStatusCode());
        $this->assertSame(400, $exception->getCode());
    }

    public function testAuthenticationException(): void
    {
        $exception = new AuthenticationException('invalid_api_key', 'Invalid API key');

        $this->assertSame('[invalid_api_key] Invalid API key', $exception->getMessage());
        $this->assertSame('invalid_api_key', $exception->getErrorCode());
        $this->assertSame(401, $exception->getStatusCode());
        $this->assertSame(401, $exception->getCode());
    }

    public function testValidationException(): void
    {
        $exception = new ValidationException('invalid_email_format', 'Invalid email');

        $this->assertSame('[invalid_email_format] Invalid email', $exception->getMessage());
        $this->assertSame('invalid_email_format', $exception->getErrorCode());
        $this->assertSame(400, $exception->getStatusCode());
        $this->assertSame(400, $exception->getCode());
    }

    public function testQuotaExceededException(): void
    {
        $exception = new QuotaExceededException('quota_exceeded', 'Quota exceeded');

        $this->assertSame('[quota_exceeded] Quota exceeded', $exception->getMessage());
        $this->assertSame('quota_exceeded', $exception->getErrorCode());
        $this->assertSame(429, $exception->getStatusCode());
        $this->assertSame(429, $exception->getCode());
    }

    public function testServerException(): void
    {
        $exception = new ServerException('internal_server_error', 'Server error', 500);

        $this->assertSame('[internal_server_error] Server error', $exception->getMessage());
        $this->assertSame('internal_server_error', $exception->getErrorCode());
        $this->assertSame(500, $exception->getStatusCode());
        $this->assertSame(500, $exception->getCode());
    }
}
