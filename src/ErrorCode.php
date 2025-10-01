<?php

declare(strict_types=1);

namespace MailSniper;

/**
 * API error codes as defined in the MailSniper API documentation.
 */
final class ErrorCode
{
    // Authentication & Authorization
    public const AUTHENTICATION_REQUIRED = 'authentication_required';
    public const INVALID_API_KEY = 'invalid_api_key';
    public const API_KEY_FORMAT_INVALID = 'api_key_format_invalid';
    public const ACCESS_DENIED = 'access_denied';

    // Validation Errors
    public const INVALID_EMAIL_FORMAT = 'invalid_email_format';
    public const EMPTY_EMAIL = 'empty_email';
    public const INVALID_PARAMETERS = 'invalid_parameters';
    public const BAD_REQUEST = 'bad_request';

    // Rate Limiting & Quota
    public const QUOTA_EXCEEDED = 'quota_exceeded';
    public const RATE_LIMIT_EXCEEDED = 'rate_limit_exceeded';
    public const INSUFFICIENT_QUOTA = 'insufficient_quota';
    public const QUOTA_CONSUMPTION_FAILED = 'quota_consumption_failed';

    // HTTP Errors
    public const ENDPOINT_NOT_FOUND = 'endpoint_not_found';
    public const METHOD_NOT_ALLOWED = 'method_not_allowed';

    // System Errors
    public const INTERNAL_SERVER_ERROR = 'internal_server_error';
    public const SERVICE_UNAVAILABLE = 'service_unavailable';
}
