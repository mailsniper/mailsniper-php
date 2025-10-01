<?php

declare(strict_types=1);

namespace MailSniper\Exception;

/**
 * Exception thrown when authentication fails (401 Unauthorized).
 */
class AuthenticationException extends ApiException
{
    /**
     * @param string $errorCode The error code from the API
     * @param string $message The error message from the API
     */
    public function __construct(string $errorCode, string $message)
    {
        parent::__construct($errorCode, $message, 401);
    }
}
