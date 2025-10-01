<?php

declare(strict_types=1);

namespace MailSniper\Model;

/**
 * DNS information for an email domain.
 */
class DnsInfo
{
    /**
     * @param string[] $mxServers List of MX servers for the domain
     * @param bool $hasARootRecord Whether the domain has an A record
     */
    public function __construct(
        private readonly array $mxServers,
        private readonly bool $hasARootRecord
    ) {
    }

    /**
     * Get the list of MX servers.
     *
     * @return string[]
     */
    public function getMxServers(): array
    {
        return $this->mxServers;
    }

    /**
     * Check if the domain has an A record.
     *
     * @return bool
     */
    public function hasARootRecord(): bool
    {
        return $this->hasARootRecord;
    }

    /**
     * Create a DnsInfo instance from an array.
     *
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['mx_servers'] ?? [],
            $data['has_a_root_record'] ?? false
        );
    }

    /**
     * Convert the DNS info to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'mx_servers' => $this->mxServers,
            'has_a_root_record' => $this->hasARootRecord,
        ];
    }
}
