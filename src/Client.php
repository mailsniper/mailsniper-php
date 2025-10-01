<?php

declare(strict_types=1);

namespace MailSniper;

use MailSniper\Exception\ApiException;
use MailSniper\Exception\AuthenticationException;
use MailSniper\Exception\MailSniperException;
use MailSniper\Exception\QuotaExceededException;
use MailSniper\Exception\ServerException;
use MailSniper\Exception\ValidationException;
use MailSniper\Model\EmailVerificationResult;
use MailSniper\Model\UsageInfo;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * MailSniper API Client.
 *
 * Official PHP SDK for the MailSniper Email Verification API.
 * Uses PSR-18 HTTP Client and PSR-17 HTTP Factory for flexibility.
 */
class Client
{
    private const API_KEY_PATTERN = '/^ms_[a-f0-9]{8}_[a-f0-9]{32}$/';
    private const DEFAULT_BASE_URL = 'https://api.mailsniperapp.com';
    private const USER_AGENT = 'MailSniper-PHP-SDK/1.0.0';

    private ClientInterface $httpClient;
    private RequestFactoryInterface $requestFactory;
    private string $apiKey;
    private string $baseUrl;

    /**
     * Create a new MailSniper client.
     *
     * @param string $apiKey The API key for authentication
     * @param ClientInterface $httpClient PSR-18 HTTP client
     * @param RequestFactoryInterface $requestFactory PSR-17 request factory
     * @param string $baseUrl The base URL for the API (optional)
     * @throws MailSniperException If the API key format is invalid
     */
    public function __construct(
        string $apiKey,
        ClientInterface $httpClient,
        RequestFactoryInterface $requestFactory,
        string $baseUrl = self::DEFAULT_BASE_URL
    ) {
        $this->validateApiKeyFormat($apiKey);

        $this->apiKey = $apiKey;
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    /**
     * Create a new client with auto-discovered HTTP client (Guzzle by default).
     *
     * This factory method uses PSR-18 HTTP Discovery to automatically find
     * and instantiate an available HTTP client implementation.
     *
     * @param string $apiKey The API key for authentication
     * @param string $baseUrl The base URL for the API (optional)
     * @return self
     * @throws MailSniperException If the API key format is invalid or no HTTP client is found
     */
    public static function create(string $apiKey, string $baseUrl = self::DEFAULT_BASE_URL): self
    {
        if (!class_exists('Http\Discovery\Psr18ClientDiscovery')) {
            throw new MailSniperException(
                'HTTP client discovery requires php-http/discovery package. ' .
                'Install it with: composer require php-http/discovery'
            );
        }

        try {
            /** @var ClientInterface $httpClient */
            $httpClient = \Http\Discovery\Psr18ClientDiscovery::find();

            /** @var RequestFactoryInterface $requestFactory */
            $requestFactory = \Http\Discovery\Psr17FactoryDiscovery::findRequestFactory();

            return new self($apiKey, $httpClient, $requestFactory, $baseUrl);
        } catch (\Throwable $e) {
            throw new MailSniperException(
                'Failed to discover HTTP client. Please provide a PSR-18 client manually or install guzzlehttp/guzzle.',
                0,
                $e
            );
        }
    }

    /**
     * Verify an email address.
     *
     * @param string $email The email address to verify
     * @return EmailVerificationResult
     * @throws MailSniperException
     */
    public function verifyEmail(string $email): EmailVerificationResult
    {
        $encodedEmail = rawurlencode($email);
        $response = $this->request('GET', "/v1/verify/email/{$encodedEmail}");

        return EmailVerificationResult::fromArray(
            $response['body'],
            $response['headers']
        );
    }

    /**
     * Get API usage information.
     *
     * @return UsageInfo
     * @throws MailSniperException
     */
    public function getUsage(): UsageInfo
    {
        $response = $this->request('GET', '/v1/usage');

        return UsageInfo::fromArray($response['body']);
    }

    /**
     * Make an HTTP request to the API.
     *
     * @param string $method HTTP method
     * @param string $path API endpoint path
     * @return array{body: array<string, mixed>, headers: array<string, string>}
     * @throws MailSniperException
     */
    private function request(string $method, string $path): array
    {
        $uri = $this->baseUrl . $path;

        try {
            $request = $this->createRequest($method, $uri);
            $response = $this->httpClient->sendRequest($request);

            return $this->handleResponse($response);
        } catch (ClientExceptionInterface $e) {
            throw new MailSniperException(
                'HTTP client error: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Create a PSR-7 request with authentication headers.
     *
     * @param string $method HTTP method
     * @param string $uri Full URI
     * @return RequestInterface
     */
    private function createRequest(string $method, string $uri): RequestInterface
    {
        $request = $this->requestFactory->createRequest($method, $uri);

        return $request
            ->withHeader('Authorization', 'Bearer ' . $this->apiKey)
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Accept', 'application/json')
            ->withHeader('User-Agent', self::USER_AGENT);
    }

    /**
     * Handle HTTP response.
     *
     * @param ResponseInterface $response
     * @return array{body: array<string, mixed>, headers: array<string, string>}
     * @throws MailSniperException
     */
    private function handleResponse(ResponseInterface $response): array
    {
        $statusCode = $response->getStatusCode();
        $body = (string) $response->getBody();
        $headers = $this->normalizeHeaders($response->getHeaders());

        /** @var array<string, mixed> $data */
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new MailSniperException(
                'Failed to parse JSON response: ' . json_last_error_msg()
            );
        }

        if ($statusCode >= 200 && $statusCode < 300) {
            return [
                'body' => $data,
                'headers' => $headers,
            ];
        }

        $this->handleErrorResponse($data, $statusCode);
    }

    /**
     * Handle error responses from the API.
     *
     * @param array<string, mixed> $data
     * @param int $statusCode
     * @return never
     * @throws ApiException
     */
    private function handleErrorResponse(array $data, int $statusCode): never
    {
        $errorCode = $data['error_code'] ?? 'unknown_error';
        $message = $data['message'] ?? 'An unknown error occurred';

        // Authentication errors (401)
        if ($statusCode === 401) {
            throw new AuthenticationException($errorCode, $message);
        }

        // Validation errors (400)
        if ($statusCode === 400) {
            throw new ValidationException($errorCode, $message);
        }

        // Quota errors (429)
        if ($statusCode === 429) {
            throw new QuotaExceededException($errorCode, $message);
        }

        // Server errors (5xx)
        if ($statusCode >= 500) {
            throw new ServerException($errorCode, $message, $statusCode);
        }

        // Other client errors (4xx)
        throw new ApiException($errorCode, $message, $statusCode);
    }

    /**
     * Normalize response headers.
     *
     * @param array<string, string[]> $headers
     * @return array<string, string>
     */
    private function normalizeHeaders(array $headers): array
    {
        $normalized = [];

        foreach ($headers as $name => $values) {
            $normalized[strtolower($name)] = is_array($values) ? $values[0] : $values;
        }

        return $normalized;
    }

    /**
     * Validate the API key format.
     *
     * @param string $apiKey
     * @throws MailSniperException
     */
    private function validateApiKeyFormat(string $apiKey): void
    {
        if (!preg_match(self::API_KEY_PATTERN, $apiKey)) {
            throw new MailSniperException(
                'Invalid API key format. Expected format: ms_[8 hex chars]_[32 hex chars]'
            );
        }
    }

    /**
     * Get the current API key.
     *
     * @return string
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * Get the base URL.
     *
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }
}
