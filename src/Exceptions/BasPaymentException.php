<?php

namespace Bas\LaravelPayment\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Exception thrown when BAS Payment Gateway operations fail.
 */
class BasPaymentException extends RuntimeException
{
    /**
     * Additional context data for the exception.
     *
     * @var array<string, mixed>
     */
    protected array $context;

    /**
     * Create a new BasPaymentException instance.
     *
     * @param string $message Error message
     * @param int $code Error code
     * @param array<string, mixed> $context Additional context data
     * @param Throwable|null $previous Previous exception
     */
    public function __construct(
        string $message = "",
        int $code = 0,
        array $context = [],
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    /**
     * Get the exception context data.
     *
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Create an exception for configuration errors.
     *
     * @param string $message
     * @return static
     */
    public static function configurationError(string $message): static
    {
        return new static("Configuration Error: {$message}", 1001);
    }

    /**
     * Create an exception for API communication errors.
     *
     * @param string $message
     * @param array<string, mixed> $context
     * @return static
     */
    public static function apiError(string $message, array $context = []): static
    {
        return new static("API Error: {$message}", 2001, $context);
    }

    /**
     * Create an exception for signature verification errors.
     *
     * @param string $message
     * @return static
     */
    public static function signatureError(string $message = 'Signature verification failed'): static
    {
        return new static("Security Error: {$message}", 3001);
    }

    /**
     * Create an exception for encryption/decryption errors.
     *
     * @param string $message
     * @return static
     */
    public static function encryptionError(string $message): static
    {
        return new static("Encryption Error: {$message}", 3002);
    }
}
