<?php

declare(strict_types=1);

namespace MailSniper\Exception;

/**
 * Exception thrown when a server error occurs (5xx status codes).
 */
class ServerException extends ApiException
{
    /**
     * @param string $errorCode The error code from the API
     * @param string $message The error message from the API
     * @param int $statusCode The HTTP status code
     */
    public function __construct(string $errorCode, string $message, int $statusCode)
    {
        parent::__construct($errorCode, $message, $statusCode);
    }
}
