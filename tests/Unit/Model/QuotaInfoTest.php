<?php

declare(strict_types=1);

namespace MailSniper\Tests\Unit\Model;

use MailSniper\Model\QuotaInfo;
use PHPUnit\Framework\TestCase;

class QuotaInfoTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $quota = new QuotaInfo(10000, 2500, 7500);

        $this->assertSame(10000, $quota->getTotal());
        $this->assertSame(2500, $quota->getUsed());
        $this->assertSame(7500, $quota->getRemaining());
    }

    public function testGetPercentageUsed(): void
    {
        $quota = new QuotaInfo(10000, 2500, 7500);

        $this->assertSame(25.0, $quota->getPercentageUsed());
    }

    public function testGetPercentageUsedWithZeroTotal(): void
    {
        $quota = new QuotaInfo(0, 0, 0);

        $this->assertSame(0.0, $quota->getPercentageUsed());
    }

    public function testIsApproachingLimitTrue(): void
    {
        $quota = new QuotaInfo(10000, 8500, 1500);

        $this->assertTrue($quota->isApproachingLimit());
    }

    public function testIsApproachingLimitFalse(): void
    {
        $quota = new QuotaInfo(10000, 7500, 2500);

        $this->assertFalse($quota->isApproachingLimit());
    }

    public function testFromHeaders(): void
    {
        $headers = [
            'x-ratelimit-quota-total' => '10000',
            'x-ratelimit-quota-used' => '2500',
            'x-ratelimit-quota-remaining' => '7500',
        ];

        $quota = QuotaInfo::fromHeaders($headers);

        $this->assertSame(10000, $quota->getTotal());
        $this->assertSame(2500, $quota->getUsed());
        $this->assertSame(7500, $quota->getRemaining());
    }

    public function testFromHeadersWithMissingHeaders(): void
    {
        $quota = QuotaInfo::fromHeaders([]);

        $this->assertSame(0, $quota->getTotal());
        $this->assertSame(0, $quota->getUsed());
        $this->assertSame(0, $quota->getRemaining());
    }

    public function testToArray(): void
    {
        $quota = new QuotaInfo(10000, 2500, 7500);

        $expected = [
            'total' => 10000,
            'used' => 2500,
            'remaining' => 7500,
            'percentage_used' => 25.0,
            'is_approaching_limit' => false,
        ];

        $this->assertSame($expected, $quota->toArray());
    }
}
