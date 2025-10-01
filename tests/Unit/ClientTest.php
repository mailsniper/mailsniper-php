<?php

declare(strict_types=1);

namespace MailSniper\Tests\Unit;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Response;
use MailSniper\Client;
use MailSniper\Exception\AuthenticationException;
use MailSniper\Exception\MailSniperException;
use MailSniper\Exception\QuotaExceededException;
use MailSniper\Exception\ServerException;
use MailSniper\Exception\ValidationException;
use MailSniper\Model\EmailVerificationResult;
use MailSniper\Model\UsageInfo;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    private const VALID_API_KEY = 'ms_12345678_abcdef1234567890abcdef1234567890';

    public function testConstructorWithValidApiKey(): void
    {
        $client = $this->createClient();

        $this->assertInstanceOf(Client::class, $client);
        $this->assertSame(self::VALID_API_KEY, $client->getApiKey());
        $this->assertSame('https://api.mailsniperapp.com', $client->getBaseUrl());
    }

    public function testConstructorWithCustomBaseUrl(): void
    {
        $httpFactory = new HttpFactory();
        $mock = new MockHandler([]);
        $httpClient = new GuzzleClient(['handler' => HandlerStack::create($mock)]);

        $client = new Client(
            self::VALID_API_KEY,
            $httpClient,
            $httpFactory,
            'https://custom.api.example.com'
        );

        $this->assertSame('https://custom.api.example.com', $client->getBaseUrl());
    }

    public function testConstructorWithInvalidApiKeyFormat(): void
    {
        $this->expectException(MailSniperException::class);
        $this->expectExceptionMessage('Invalid API key format');

        $httpFactory = new HttpFactory();
        $mock = new MockHandler([]);
        $httpClient = new GuzzleClient(['handler' => HandlerStack::create($mock)]);

        new Client('invalid_api_key', $httpClient, $httpFactory);
    }

    /**
     * @dataProvider invalidApiKeyProvider
     */
    public function testConstructorWithVariousInvalidApiKeys(string $invalidKey): void
    {
        $this->expectException(MailSniperException::class);

        $httpFactory = new HttpFactory();
        $mock = new MockHandler([]);
        $httpClient = new GuzzleClient(['handler' => HandlerStack::create($mock)]);

        new Client($invalidKey, $httpClient, $httpFactory);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function invalidApiKeyProvider(): array
    {
        return [
            'missing prefix' => ['abc12345_abcdef1234567890abcdef1234567890'],
            'wrong prefix' => ['mt_12345678_abcdef1234567890abcdef1234567890'],
            'short identifier' => ['ms_1234567_abcdef1234567890abcdef1234567890'],
            'short secret' => ['ms_12345678_abcdef1234567890abcdef123456789'],
            'non-hex chars' => ['ms_1234567g_abcdef1234567890abcdef1234567890'],
        ];
    }

    public function testVerifyEmailSuccess(): void
    {
        $responseBody = [
            'email' => 'test@example.com',
            'user' => 'test',
            'domain' => 'example.com',
            'is_valid' => true,
            'is_disposable' => false,
            'is_public_provider' => false,
            'is_university' => false,
            'is_spam' => false,
            'risk' => 10,
            'dns' => [
                'mx_servers' => ['mail.example.com'],
                'has_a_root_record' => true,
            ],
        ];

        $mock = new MockHandler([
            new Response(200, [
                'Content-Type' => 'application/json',
                'X-RateLimit-Quota-Total' => '10000',
                'X-RateLimit-Quota-Used' => '100',
                'X-RateLimit-Quota-Remaining' => '9900',
            ], json_encode($responseBody)),
        ]);

        $client = $this->createClientWithMockHandler($mock);
        $result = $client->verifyEmail('test@example.com');

        $this->assertInstanceOf(EmailVerificationResult::class, $result);
        $this->assertSame('test@example.com', $result->getEmail());
        $this->assertTrue($result->isValid());
        $this->assertSame(10, $result->getRisk());
        $this->assertNotNull($result->getQuota());
        $this->assertSame(10000, $result->getQuota()->getTotal());
    }

    public function testVerifyEmailWithAuthenticationError(): void
    {
        $mock = new MockHandler([
            new Response(401, ['Content-Type' => 'application/json'], json_encode([
                'error_code' => 'invalid_api_key',
                'message' => 'Invalid API key',
            ])),
        ]);

        $client = $this->createClientWithMockHandler($mock);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid API key');

        $client->verifyEmail('test@example.com');
    }

    public function testVerifyEmailWithValidationError(): void
    {
        $mock = new MockHandler([
            new Response(400, ['Content-Type' => 'application/json'], json_encode([
                'error_code' => 'invalid_email_format',
                'message' => 'Invalid email format',
            ])),
        ]);

        $client = $this->createClientWithMockHandler($mock);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid email format');

        $client->verifyEmail('invalid-email');
    }

    public function testVerifyEmailWithQuotaExceeded(): void
    {
        $mock = new MockHandler([
            new Response(429, ['Content-Type' => 'application/json'], json_encode([
                'error_code' => 'quota_exceeded',
                'message' => 'Quota exceeded',
            ])),
        ]);

        $client = $this->createClientWithMockHandler($mock);

        $this->expectException(QuotaExceededException::class);
        $this->expectExceptionMessage('Quota exceeded');

        $client->verifyEmail('test@example.com');
    }

    public function testVerifyEmailWithServerError(): void
    {
        $mock = new MockHandler([
            new Response(500, ['Content-Type' => 'application/json'], json_encode([
                'error_code' => 'internal_server_error',
                'message' => 'Internal server error',
            ])),
        ]);

        $client = $this->createClientWithMockHandler($mock);

        $this->expectException(ServerException::class);
        $this->expectExceptionMessage('Internal server error');

        $client->verifyEmail('test@example.com');
    }

    public function testGetUsageSuccess(): void
    {
        $responseBody = [
            'total' => 10000,
            'used' => 1250,
            'remaining' => 8750,
            'percentage_used' => 12.5,
            'is_approaching_limit' => false,
        ];

        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], json_encode($responseBody)),
        ]);

        $client = $this->createClientWithMockHandler($mock);
        $usage = $client->getUsage();

        $this->assertInstanceOf(UsageInfo::class, $usage);
        $this->assertSame(10000, $usage->getTotal());
        $this->assertSame(1250, $usage->getUsed());
        $this->assertSame(8750, $usage->getRemaining());
        $this->assertSame(12.5, $usage->getPercentageUsed());
        $this->assertFalse($usage->isApproachingLimit());
    }

    public function testGetUsageWithAuthenticationError(): void
    {
        $mock = new MockHandler([
            new Response(401, ['Content-Type' => 'application/json'], json_encode([
                'error_code' => 'invalid_api_key',
                'message' => 'Invalid API key',
            ])),
        ]);

        $client = $this->createClientWithMockHandler($mock);

        $this->expectException(AuthenticationException::class);

        $client->getUsage();
    }

    private function createClient(): Client
    {
        $httpFactory = new HttpFactory();
        $mock = new MockHandler([]);
        $httpClient = new GuzzleClient(['handler' => HandlerStack::create($mock)]);

        return new Client(self::VALID_API_KEY, $httpClient, $httpFactory);
    }

    private function createClientWithMockHandler(MockHandler $mock): Client
    {
        $httpFactory = new HttpFactory();
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new GuzzleClient(['handler' => $handlerStack]);

        return new Client(self::VALID_API_KEY, $httpClient, $httpFactory);
    }
}
