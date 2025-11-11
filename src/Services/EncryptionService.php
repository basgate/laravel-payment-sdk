<?php

namespace Bas\LaravelPayment\Services;

use Bas\LaravelPayment\Exceptions\BasPaymentException;
use Exception;
use InvalidArgumentException;
use RuntimeException;

class EncryptionService
{
    protected ?string $merchantKey;
    protected ?string $iv;

    /**
     * Create a new EncryptionService instance.
     *
     * @throws InvalidArgumentException
     */
    public function __construct()
    {
        $this->iv = config('bas-payment.iv');
        $this->merchantKey = config('bas-payment.merchant_key');

        $this->validateConfiguration();
    }

    /**
     * Validate required configuration values.
     *
     * @throws BasPaymentException
     */
    protected function validateConfiguration(): void
    {
        if (empty($this->merchantKey)) {
            throw BasPaymentException::configurationError('BAS merchant key is not configured. Please set BAS_MERCHANT_KEY in your .env file.');
        }

        if (empty($this->iv)) {
            throw BasPaymentException::configurationError('BAS IV is not configured. This should use the default value provided by BAS.');
        }

        if (strlen($this->iv) !== 16) {
            throw BasPaymentException::configurationError('BAS IV must be exactly 16 bytes for AES-256-CBC encryption. Current length: ' . strlen($this->iv));
        }
    }

    /**
     * Generate a signature for the given parameters.
     *
     * @param array<string, mixed>|string $params
     * @return string
     * @throws InvalidArgumentException
     */
    public function generateSignature(array|string $params): string
    {
        if (!is_array($params) && !is_string($params)) {
            throw new InvalidArgumentException("String or array expected, " . gettype($params) . " given");
        }

        if (is_array($params)) {
            $params = $this->getStringByParams($params);
        }

        return $this->generateSignatureByString($params);
    }

    /**
     * Convert parameters array to string format.
     *
     * @param array<string, mixed> $params
     * @return string
     */
    private function getStringByParams(array $params): string
    {
        ksort($params);
        $params = array_map(function ($value) {
            return ($value !== null && strtolower((string)$value) !== "null") ? $value : "";
        }, $params);
        return implode("|", $params);
    }

    /**
     * Generate signature from string parameters.
     *
     * @param string $params
     * @return string
     */
    private function generateSignatureByString(string $params): string
    {
        $salt = $this->generateRandomString(4);
        return $this->calculateChecksum($params, $salt);
    }

    /**
     * Generate a random string of specified length.
     *
     * @param int $length
     * @return string
     */
    private function generateRandomString(int $length): string
    {
        $data = "9876543210ZYXWVUTSRQPONMLKJIHGFEDCBAabcdefghijklmnopqrstuvwxyz!@#$&_";
        return substr(str_shuffle(str_repeat($data, $length)), 0, $length);
    }

    /**
     * Calculate checksum for parameters and salt.
     *
     * @param string $params
     * @param string $salt
     * @return string
     */
    private function calculateChecksum(string $params, string $salt): string
    {
        $hashString = $this->calculateHash($params, $salt);
        return $this->encrypt($hashString);
    }

    /**
     * Calculate hash for parameters and salt.
     *
     * @param string $params
     * @param string $salt
     * @return string
     */
    private function calculateHash(string $params, string $salt): string
    {
        return hash("sha256", $params . "|" . $salt) . $salt;
    }

    /**
     * Encrypt input string using AES-256-CBC.
     *
     * @param string $input
     * @return string
     * @throws BasPaymentException
     */
    private function encrypt(string $input): string
    {
        $key = html_entity_decode($this->merchantKey);
        $password = substr(hash('sha256', $key, true), 0, 32);
        
        $data = openssl_encrypt($input, 'aes-256-cbc', $password, OPENSSL_RAW_DATA, $this->iv);
        
        if ($data === false) {
            throw BasPaymentException::encryptionError('Encryption failed: ' . openssl_error_string());
        }
        
        return base64_encode($data);
    }

    /**
     * Verify signature for given parameters and checksum.
     *
     * @param array<string, mixed>|string $params
     * @param string $checksum
     * @return bool
     * @throws InvalidArgumentException
     */
    public function verifySignature(array|string $params, string $checksum): bool
    {
        if (!is_array($params) && !is_string($params)) {
            throw new InvalidArgumentException("String or array expected, " . gettype($params) . " given");
        }

        if (is_array($params)) {
            if (isset($params['CHECKSUMHASH'])) {
                unset($params['CHECKSUMHASH']);
            }
            $params = $this->getStringByParams($params);
        }

        return $this->verifySignatureByString($params, $checksum);
    }

    /**
     * Verify signature by string parameters.
     *
     * @param string $params
     * @param string $checksum
     * @return bool
     */
    private function verifySignatureByString(string $params, string $checksum): bool
    {
        try {
            $bas_hash = $this->decrypt($checksum);
            $salt = substr($bas_hash, -4);
            return $bas_hash === $this->calculateHash($params, $salt);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Decrypt encrypted string using AES-256-CBC.
     *
     * @param string $encrypted
     * @return string
     * @throws BasPaymentException
     */
    private function decrypt(string $encrypted): string
    {
        $key = html_entity_decode($this->merchantKey);
        $password = substr(hash('sha256', $key, true), 0, 32);
        
        $decrypted = openssl_decrypt($encrypted, "aes-256-cbc", $password, 0, $this->iv);
        
        if ($decrypted === false) {
            throw BasPaymentException::encryptionError('Decryption failed: ' . openssl_error_string());
        }
        
        return $decrypted;
    }
}
