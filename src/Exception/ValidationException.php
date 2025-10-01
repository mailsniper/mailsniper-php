<?php

declare(strict_types=1);

namespace MailSniper\Exception;

/**
 * Exception thrown when request validation fails (400 Bad Request).
 */
class ValidationException extends ApiException
{
    /**
     * @param string $errorCode The error code from the API
     * @param string $message The error message from the API
     */
    public function __construct(string $errorCode, string $message)
    {
        parent::__construct($errorCode, $message, 400);
    }
}
