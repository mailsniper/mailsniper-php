<?php

declare(strict_types=1);

namespace MailSniper\Tests\Unit\Model;

use MailSniper\Model\DnsInfo;
use MailSniper\Model\EmailVerificationResult;
use MailSniper\Model\QuotaInfo;
use PHPUnit\Framework\TestCase;

class EmailVerificationResultTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $dns = new DnsInfo(['mail.example.com'], true);
        $quota = new QuotaInfo(10000, 100, 9900);

        $result = new EmailVerificationResult(
            'test@example.com',
            'test',
            'example.com',
            true,
            false,
            false,
            false,
            false,
            10,
            $dns,
            $quota
        );

        $this->assertSame('test@example.com', $result->getEmail());
        $this->assertSame('test', $result->getUser());
        $this->assertSame('example.com', $result->getDomain());
        $this->assertTrue($result->isValid());
        $this->assertFalse($result->isDisposable());
        $this->assertFalse($result->isPublicProvider());
        $this->assertFalse($result->isUniversity());
        $this->assertFalse($result->isSpam());
        $this->assertSame(10, $result->getRisk());
        $this->assertSame($dns, $result->getDns());
        $this->assertSame($quota, $result->getQuota());
    }

    public function testFromArrayWithHeaders(): void
    {
        $data = [
            'email' => 'test@example.com',
            'user' => 'test',
            'domain' => 'example.com',
            'is_valid' => true,
            'is_disposable' => false,
            'is_public_provider' => false,
            'is_university' => false,
            'is_spam' => false,
            'risk' => 15,
            'dns' => [
                'mx_servers' => ['mail.example.com'],
                'has_a_root_record' => true,
            ],
        ];

        $headers = [
            'x-ratelimit-quota-total' => '10000',
            'x-ratelimit-quota-used' => '100',
            'x-ratelimit-quota-remaining' => '9900',
        ];

        $result = EmailVerificationResult::fromArray($data, $headers);

        $this->assertSame('test@example.com', $result->getEmail());
        $this->assertSame('test', $result->getUser());
        $this->assertSame('example.com', $result->getDomain());
        $this->assertTrue($result->isValid());
        $this->assertSame(15, $result->getRisk());
        $this->assertNotNull($result->getQuota());
        $this->assertSame(10000, $result->getQuota()->getTotal());
    }

    public function testFromArrayWithoutHeaders(): void
    {
        $data = [
            'email' => 'test@example.com',
            'user' => 'test',
            'domain' => 'example.com',
            'is_valid' => true,
            'is_disposable' => false,
            'is_public_provider' => false,
            'is_university' => false,
            'is_spam' => false,
            'risk' => 15,
            'dns' => [
                'mx_servers' => [],
                'has_a_root_record' => false,
            ],
        ];

        $result = EmailVerificationResult::fromArray($data);

        $this->assertSame('test@example.com', $result->getEmail());
        $this->assertNull($result->getQuota());
    }

    public function testToArray(): void
    {
        $dns = new DnsInfo(['mail.example.com'], true);
        $quota = new QuotaInfo(10000, 100, 9900);

        $result = new EmailVerificationResult(
            'test@example.com',
            'test',
            'example.com',
            true,
            false,
            false,
            false,
            false,
            10,
            $dns,
            $quota
        );

        $array = $result->toArray();

        $this->assertSame('test@example.com', $array['email']);
        $this->assertSame('test', $array['user']);
        $this->assertSame('example.com', $array['domain']);
        $this->assertTrue($array['is_valid']);
        $this->assertSame(10, $array['risk']);
        $this->assertIsArray($array['dns']);
        $this->assertIsArray($array['quota']);
    }

    public function testToArrayWithoutQuota(): void
    {
        $dns = new DnsInfo(['mail.example.com'], true);

        $result = new EmailVerificationResult(
            'test@example.com',
            'test',
            'example.com',
            true,
            false,
            false,
            false,
            false,
            10,
            $dns,
            null
        );

        $array = $result->toArray();

        $this->assertArrayNotHasKey('quota', $array);
    }
}
