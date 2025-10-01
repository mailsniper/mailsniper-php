<?php

declare(strict_types=1);

namespace MailSniper\Model;

/**
 * API usage information.
 */
class UsageInfo
{
    /**
     * @param int $total Total quota allocated
     * @param int $used Number of requests used
     * @param int $remaining Number of requests remaining
     * @param float $percentageUsed Percentage of quota used
     * @param bool $isApproachingLimit Whether approaching the quota limit
     */
    public function __construct(
        private readonly int $total,
        private readonly int $used,
        private readonly int $remaining,
        private readonly float $percentageUsed,
        private readonly bool $isApproachingLimit
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
        return $this->percentageUsed;
    }

    /**
     * Check if approaching quota limit (>= 80%).
     *
     * @return bool
     */
    public function isApproachingLimit(): bool
    {
        return $this->isApproachingLimit;
    }

    /**
     * Create a UsageInfo instance from an array.
     *
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['total'] ?? 0,
            $data['used'] ?? 0,
            $data['remaining'] ?? 0,
            $data['percentage_used'] ?? 0.0,
            $data['is_approaching_limit'] ?? false
        );
    }

    /**
     * Convert the usage info to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'total' => $this->total,
            'used' => $this->used,
            'remaining' => $this->remaining,
            'percentage_used' => $this->percentageUsed,
            'is_approaching_limit' => $this->isApproachingLimit,
        ];
    }
}
