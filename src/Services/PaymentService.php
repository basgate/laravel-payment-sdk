<?php

namespace Bas\LaravelPayment\Services;

use Bas\LaravelPayment\Exceptions\BasPaymentException;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use JsonException;
use RuntimeException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\ConnectionException;
use InvalidArgumentException;

class PaymentService
{
    protected string $baseUrl;
    protected ?string $clientId;
    protected ?string $clientSecret;
    protected ?string $appId;
    protected EncryptionService $encryptionService;
    protected array $header;
    protected string $tokenEndpoint;
    protected string $refundPaymentEndpoint;
    protected string $transactionStatusEndpoint;
    protected string $transactionInitiateEndpoint;
    protected string $environment;

    /**
     * Create a new PaymentService instance.
     *
     * @param EncryptionService $encryptionService
     * @throws InvalidArgumentException
     */
    public function __construct(EncryptionService $encryptionService)
    {
        $this->encryptionService = $encryptionService;

        $this->baseUrl = config('bas-payment.base_url');
        $this->clientId = config('bas-payment.client_id');
        $this->clientSecret = config('bas-payment.client_secret');
        $this->appId = config('bas-payment.app_id');
        $this->environment = config('bas-payment.environment', 'staging');
        $this->tokenEndpoint = config('bas-payment.token_endpoint');
        $this->refundPaymentEndpoint = config('bas-payment.refund_payment_endpoint');
        $this->transactionStatusEndpoint = config('bas-payment.transaction_status_endpoint');
        $this->transactionInitiateEndpoint = config('bas-payment.transaction_initiate_endpoint');

        $this->validateConfiguration();

        $this->header = [
            'Content-Type' => 'application/json',
            'x-client-id' => $this->clientId,
            'x-app-id' => $this->appId,
            'x-sdk-version' => $this->getSdkVersion(),
            'x-environment' => $this->environment,
            'correlationId' => '',
            'x-sdk-type' => 'Laravel',
        ];
    }

    /**
     * Validate required configuration values.
     *
     * @throws BasPaymentException
     */
    protected function validateConfiguration(): void
    {
        $requiredConfigs = [
            'base_url' => $this->baseUrl,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'app_id' => $this->appId,
        ];

        foreach ($requiredConfigs as $key => $value) {
            if (empty($value)) {
                throw BasPaymentException::configurationError("BAS {$key} is not configured. Please set BAS_" . strtoupper($key) . " in your .env file.");
            }
        }
    }





    /**
     * Refund a payment transaction.
     *
     * @param string $trxToken Transaction token to refund
     * @param string $reason Reason for the refund
     * @return array<string, mixed> API response
     * @throws Exception
     */
    public function refund(string $trxToken, string $reason = 'Refund requested'): array
    {
        $body = [
            'trxToken' => $trxToken,
            'reason' => $reason
        ];

        return $this->callApi(
            $this->refundPaymentEndpoint,
            $body,
            ['as_form' => false],
            true,
            'POST'
        );
    }

    /**
     * Check the status of a transaction.
     *
     * @param string $orderId Order ID to check
     * @return array<string, mixed> Transaction status response
     * @throws JsonException
     * @throws Exception
     */
    public function checkTransactionStatus(string $orderId): array
    {
        $requestTimestamp = time() * 1000;

        $body = [
            'appId' => $this->appId,
            'orderId' => $orderId,
            'requestTimestamp' => $requestTimestamp
        ];

        $encodeBody = json_encode($body, JSON_THROW_ON_ERROR);
        $signature = $this->encryptionService->generateSignature($encodeBody);

        $data = [
            'head' => [
                'signature' => $signature,
                'requestTimestamp' => $requestTimestamp,
            ],
            'body' => $body
        ];

        $response = $this->callApi(
            $this->transactionStatusEndpoint,
            $data,
            ['as_form' => false],
            true,
            'POST'
        );

        return $this->handleTransactionResponse($response);
    }


    /**
     * Initiate a new payment transaction.
     *
     * @param string $orderId Unique order ID
     * @param float|int $amount Payment amount
     * @param string $currency Currency code (e.g., 'USD', 'EUR')
     * @param string $orderType Order type (default: 'PayBill')
     * @return array<string, mixed> Transaction response
     * @throws JsonException
     * @throws Exception
     */
    public function initiateTransaction(
        string $orderId,
        float|int $amount,
        string $currency,
        string $orderType = 'PayBill'
    ): array {
        $requestTimestamp = time() * 1000;

        $body = [
            'amount' => [
                'value' => $amount,
                'currency' => $currency
            ],
            'ordertype' => $orderType,
            'orderId' => $orderId,
            'requestTimestamp' => $requestTimestamp,
            'appId' => $this->appId
        ];

        $signature = $this->encryptionService->generateSignature(json_encode($body, JSON_THROW_ON_ERROR));

        $data = [
            'head' => [
                'signature' => $signature,
                'requestTimestamp' => $requestTimestamp
            ],
            'body' => $body
        ];

        $response = $this->callApi(
            $this->transactionInitiateEndpoint,
            $data,
            ['as_form' => false],
            true,
            'POST'
        );

        return $this->handleTransactionResponse($response);
    }



    /**
     * Get SDK version from composer.json or return default.
     *
     * @return string SDK version
     */
    private function getSdkVersion(): string
    {
        try {
            if (class_exists('Composer\InstalledVersions')) {
                $version = \Composer\InstalledVersions::getPrettyVersion('basgate/laravel-payment-sdk');
                if ($version !== null) {
                    return $version;
                }
            }
        } catch (\Exception $e) {
            // Composer class not available or package not found
        }

        // Fallback: Try to read from composer.json
        $composerFile = dirname(__DIR__, 2) . '/composer.json';
        if (file_exists($composerFile)) {
            $content = file_get_contents($composerFile);
            if ($content !== false) {
                $composer = json_decode($content, true);
                if (isset($composer['version'])) {
                    return $composer['version'];
                }
            }
        }

        return '1.0.0';
    }

    /**
     * Request a new OAuth access token.
     *
     * @return string Access token
     * @throws Exception
     */
    public function requestNewToken(): string
    {
        $body = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'client_credentials',
        ];

        $response = $this->callApi(
            $this->tokenEndpoint,
            $body,
            ['as_form' => true]
        );

        if (!isset($response['access_token'])) {
            throw new RuntimeException('Failed to retrieve access token from response');
        }

        return $response['access_token'];
    }
    /**
     * Handle and validate transaction response.
     *
     * @param array<string, mixed> $response API response
     * @return array<string, mixed> Validated response
     * @throws BasPaymentException
     */
    private function handleTransactionResponse(array $response): array
    {
        // Successful transaction with signature verification
        if (isset($response['status']) && $response['status'] === 1 &&
            isset($response['code']) && $response['code'] === '1111') {

            if (!isset($response['body']['trxToken'], $response['body']['trxStatus'],
                       $response['body']['order']['orderId'], $response['head']['signature'])) {
                throw BasPaymentException::apiError('Invalid transaction response structure', $response);
            }

            $params = $response['body']['trxToken'] .
                     $response['body']['trxStatus'] .
                     $response['body']['order']['orderId'];

            if ($this->encryptionService->verifySignature($params, $response['head']['signature'])) {
                return $response;
            }

            throw BasPaymentException::signatureError('Signature verification failed for transaction response');
        }

        // Failed transaction
        if (isset($response['status']) && $response['status'] === 0) {
            return $response;
        }

        throw BasPaymentException::apiError('Transaction failed', $response);
    }




    /**
     * Make an API call to the BAS payment gateway.
     *
     * @param string $endpoint API endpoint path
     * @param array<string, mixed> $data Request data
     * @param array<string, mixed> $options Request options (as_form, timeout, etc.)
     * @param bool $token Whether to include authentication token
     * @param string $method HTTP method (GET, POST, PUT, PATCH, DELETE)
     * @return array<string, mixed> API response
     * @throws Exception
     */
    public function callApi(
        string $endpoint,
        array $data,
        array $options = [],
        bool $token = true,
        string $method = 'POST'
    ): array {
        try {
            $headers = $this->header;

            // Validate HTTP method
            $method = strtoupper($method);
            $validMethods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];
            if (!in_array($method, $validMethods, true)) {
                throw new InvalidArgumentException("Invalid HTTP method: {$method}");
            }

            // Remove Content-Type header if sending as form data
            if ($options['as_form'] ?? false) {
                unset($headers['Content-Type']);
            }

            $timeout = $options['timeout'] ?? 20;
            $http = Http::timeout($timeout)->withHeaders($headers);

            if ($options['as_form'] ?? false) {
                $http = $http->asForm();
            }

            // Add authentication token if required
            if ($token) {
                $accessToken = $this->requestNewToken();
                $http = $http->withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken
                ]);
            }

            // Log request for debugging
            $http->beforeSending(function ($request) use ($endpoint) {
                Log::debug("BAS API Request to {$endpoint}", [
                    'url' => $request->url(),
                    'method' => $request->method(),
                    'headers' => $this->sanitizeHeadersForLogging($request->headers()),
                    'body' => $request->body()
                ]);
            });

            $response = $http->$method($this->baseUrl . $endpoint, $data);

            // Log response for debugging
            Log::debug("BAS API Response from {$endpoint}", [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                $json = $response->json();
                if (!is_array($json)) {
                    throw new RuntimeException('Invalid JSON response from API');
                }
                return $json;
            }

            $errorMessage = "API Error [{$response->status()}]: " . $response->body();
            Log::error($errorMessage);
            throw new RuntimeException($errorMessage);

        } catch (ConnectionException $e) {
            $message = "Connection failed: " . $e->getMessage();
            Log::error($message);
            throw new RuntimeException($message, 0, $e);
        } catch (RequestException $e) {
            $message = "Request error: " . $e->getMessage();
            Log::error($message);
            throw new RuntimeException($message, 0, $e);
        } catch (Exception $e) {
            if ($e instanceof InvalidArgumentException || $e instanceof RuntimeException) {
                throw $e;
            }
            Log::error('Unexpected API error: ' . $e->getMessage());
            throw new RuntimeException('API communication error: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Sanitize headers for logging by removing sensitive information.
     *
     * @param array<string, mixed> $headers
     * @return array<string, mixed>
     */
    private function sanitizeHeadersForLogging(array $headers): array
    {
        $sensitiveKeys = ['authorization', 'x-client-id', 'x-app-id'];

        foreach ($headers as $key => $value) {
            if (in_array(strtolower($key), $sensitiveKeys, true)) {
                $headers[$key] = '[REDACTED]';
            }
        }

        return $headers;
    }
}
