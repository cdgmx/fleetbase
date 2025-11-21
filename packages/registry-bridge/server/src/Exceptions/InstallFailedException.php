<?php

namespace Fleetbase\RegistryBridge\Exceptions;

/**
 * Represents an exception thrown when a package installation fails.
 */
class InstallFailedException extends \Exception
{
    /**
     * Constructs the InstallFailedException.
     *
     * @param string          $message  the Exception message to throw
     * @param int             $code     the Exception code
     * @param \Throwable|null $previous the previous throwable used for the exception chaining
     */
    public function __construct($message = '', $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
