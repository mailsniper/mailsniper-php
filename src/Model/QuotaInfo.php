<?php

declare(strict_types=1);

namespace MailSniper\Model;

/**
 * Quota information for API usage.
 */
class QuotaInfo
{
    /**
     * @param int $total Total quota allocated
     * @param int $used Number of requests used
     * @param int $remaining Number of requests remaining
     */
    public function __construct(
        private readonly int $total,
        private readonly int $used,
        private readonly int $remaining
    ) {
    }

    /**
     * Get the total quota.
     *
     * @return int
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * Get the number of used requests.
     *
     * @return int
     */
    public function getUsed(): int
    {
        return $this->used;
    }

    /**
     * Get the number of remaining requests.
     *
     * @return int
     */
    public function getRemaining(): int
    {
        return $this->remaining;
    }

    /**
     * Get the percentage of quota used.
     *
     * @return float
     */
    public function getPercentageUsed(): float
    {
        if ($this->total === 0) {
            return 0.0;
        }

        return round(($this->used / $this->total) * 100, 2);
    }

    /**
     * Check if approaching quota limit (>= 80%).
     *
     * @return bool
     */
    public function isApproachingLimit(): bool
    {
        return $this->getPercentageUsed() >= 80.0;
    }

    /**
     * Create a QuotaInfo instance from response headers.
     *
     * @param array<string, string> $headers
     * @return self
     */
    public static function fromHeaders(array $headers): self
    {
        $total = (int) ($headers['x-ratelimit-quota-total'] ?? 0);
        $used = (int) ($headers['x-ratelimit-quota-used'] ?? 0);
        $remaining = (int) ($headers['x-ratelimit-quota-remaining'] ?? 0);

        return new self($total, $used, $remaining);
    }

    /**
     * Convert the quota info to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'total' => $this->total,
            'used' => $this->used,
            'remaining' => $this->remaining,
            'percentage_used' => $this->getPercentageUsed(),
            'is_approaching_limit' => $this->isApproachingLimit(),
        ];
    }
}
