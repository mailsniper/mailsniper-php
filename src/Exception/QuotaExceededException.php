<?php

declare(strict_types=1);

namespace MailSniper\Exception;

/**
 * Exception thrown when API quota is exceeded (429 Too Many Requests).
 */
class QuotaExceededException extends ApiException
{
    /**
     * @param string $errorCode The error code from the API
     * @param string $message The error message from the API
     */
    public function __construct(string $errorCode, string $message)
    {
        parent::__construct($errorCode, $message, 429);
    }
}
