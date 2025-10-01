<?php

declare(strict_types=1);

namespace MailSniper\Tests\Unit\Model;

use MailSniper\Model\DnsInfo;
use PHPUnit\Framework\TestCase;

class DnsInfoTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $mxServers = ['mail1.example.com', 'mail2.example.com'];
        $dnsInfo = new DnsInfo($mxServers, true);

        $this->assertSame($mxServers, $dnsInfo->getMxServers());
        $this->assertTrue($dnsInfo->hasARootRecord());
    }

    public function testFromArray(): void
    {
        $data = [
            'mx_servers' => ['mail.example.com'],
            'has_a_root_record' => true,
        ];

        $dnsInfo = DnsInfo::fromArray($data);

        $this->assertSame(['mail.example.com'], $dnsInfo->getMxServers());
        $this->assertTrue($dnsInfo->hasARootRecord());
    }

    public function testFromArrayWithMissingData(): void
    {
        $dnsInfo = DnsInfo::fromArray([]);

        $this->assertSame([], $dnsInfo->getMxServers());
        $this->assertFalse($dnsInfo->hasARootRecord());
    }

    public function testToArray(): void
    {
        $mxServers = ['mail.example.com'];
        $dnsInfo = new DnsInfo($mxServers, true);

        $expected = [
            'mx_servers' => $mxServers,
            'has_a_root_record' => true,
        ];

        $this->assertSame($expected, $dnsInfo->toArray());
    }
}
