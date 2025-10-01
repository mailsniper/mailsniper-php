<?php

declare(strict_types=1);

namespace MailSniper\Tests\Unit\Model;

use MailSniper\Model\UsageInfo;
use PHPUnit\Framework\TestCase;

class UsageInfoTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $usage = new UsageInfo(10000, 1250, 8750, 12.5, false);

        $this->assertSame(10000, $usage->getTotal());
        $this->assertSame(1250, $usage->getUsed());
        $this->assertSame(8750, $usage->getRemaining());
        $this->assertSame(12.5, $usage->getPercentageUsed());
        $this->assertFalse($usage->isApproachingLimit());
    }

    public function testFromArray(): void
    {
        $data = [
            'total' => 10000,
            'used' => 1250,
            'remaining' => 8750,
            'percentage_used' => 12.5,
            'is_approaching_limit' => false,
        ];

        $usage = UsageInfo::fromArray($data);

        $this->assertSame(10000, $usage->getTotal());
        $this->assertSame(1250, $usage->getUsed());
        $this->assertSame(8750, $usage->getRemaining());
        $this->assertSame(12.5, $usage->getPercentageUsed());
        $this->assertFalse($usage->isApproachingLimit());
    }

    public function testFromArrayWithMissingData(): void
    {
        $usage = UsageInfo::fromArray([]);

        $this->assertSame(0, $usage->getTotal());
        $this->assertSame(0, $usage->getUsed());
        $this->assertSame(0, $usage->getRemaining());
        $this->assertSame(0.0, $usage->getPercentageUsed());
        $this->assertFalse($usage->isApproachingLimit());
    }

    public function testToArray(): void
    {
        $usage = new UsageInfo(10000, 1250, 8750, 12.5, false);

        $expected = [
            'total' => 10000,
            'used' => 1250,
            'remaining' => 8750,
            'percentage_used' => 12.5,
            'is_approaching_limit' => false,
        ];

        $this->assertSame($expected, $usage->toArray());
    }

    public function testIsApproachingLimit(): void
    {
        $usage = new UsageInfo(10000, 8500, 1500, 85.0, true);

        $this->assertTrue($usage->isApproachingLimit());
    }
}
