<?php

declare(strict_types=1);

namespace MailSniper\Exception;

/**
 * Exception thrown when the API returns an error response.
 */
class ApiException extends MailSniperException
{
    private string $errorCode;
    private int $statusCode;

    /**
     * @param string $errorCode The error code from the API
     * @param string $message The error message from the API
     * @param int $statusCode The HTTP status code
     */
    public function __construct(string $errorCode, string $message, int $statusCode)
    {
        $this->errorCode = $errorCode;
        $this->statusCode = $statusCode;

        parent::__construct(
            sprintf('[%s] %s', $errorCode, $message),
            $statusCode
        );
    }

    /**
     * Get the API error code.
     *
     * @return string
     */
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * Get the HTTP status code.
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
