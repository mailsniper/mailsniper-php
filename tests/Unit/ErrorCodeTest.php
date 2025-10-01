<?php

declare(strict_types=1);

namespace MailSniper\Tests\Unit;

use MailSniper\ErrorCode;
use PHPUnit\Framework\TestCase;

class ErrorCodeTest extends TestCase
{
    public function testConstantsAreDefined(): void
    {
        // Authentication & Authorization
        $this->assertSame('authentication_required', ErrorCode::AUTHENTICATION_REQUIRED);
        $this->assertSame('invalid_api_key', ErrorCode::INVALID_API_KEY);
        $this->assertSame('api_key_format_invalid', ErrorCode::API_KEY_FORMAT_INVALID);
        $this->assertSame('access_denied', ErrorCode::ACCESS_DENIED);

        // Validation Errors
        $this->assertSame('invalid_email_format', ErrorCode::INVALID_EMAIL_FORMAT);
        $this->assertSame('empty_email', ErrorCode::EMPTY_EMAIL);
        $this->assertSame('invalid_parameters', ErrorCode::INVALID_PARAMETERS);
        $this->assertSame('bad_request', ErrorCode::BAD_REQUEST);

        // Rate Limiting & Quota
        $this->assertSame('quota_exceeded', ErrorCode::QUOTA_EXCEEDED);
        $this->assertSame('rate_limit_exceeded', ErrorCode::RATE_LIMIT_EXCEEDED);
        $this->assertSame('insufficient_quota', ErrorCode::INSUFFICIENT_QUOTA);
        $this->assertSame('quota_consumption_failed', ErrorCode::QUOTA_CONSUMPTION_FAILED);

        // HTTP Errors
        $this->assertSame('endpoint_not_found', ErrorCode::ENDPOINT_NOT_FOUND);
        $this->assertSame('method_not_allowed', ErrorCode::METHOD_NOT_ALLOWED);

        // System Errors
        $this->assertSame('internal_server_error', ErrorCode::INTERNAL_SERVER_ERROR);
        $this->assertSame('service_unavailable', ErrorCode::SERVICE_UNAVAILABLE);
    }
}
