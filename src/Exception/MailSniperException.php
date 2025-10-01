<?php

declare(strict_types=1);

namespace MailSniper\Exception;

use Exception;

/**
 * Base exception for all MailSniper SDK exceptions.
 */
class MailSniperException extends Exception
{
    /**
     * @param string $message The exception message
     * @param int $code The exception code
     * @param \Throwable|null $previous The previous exception
     */
    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
