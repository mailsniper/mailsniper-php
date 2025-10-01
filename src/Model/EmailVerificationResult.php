<?php

declare(strict_types=1);

namespace MailSniper\Model;

/**
 * Result of an email verification request.
 */
class EmailVerificationResult
{
    /**
     * @param string $email The complete email address
     * @param string $user The username/local part
     * @param string $domain The domain part
     * @param bool $isValid Whether the email is valid
     * @param bool $isDisposable Whether the email is from a disposable provider
     * @param bool $isPublicProvider Whether the email is from a public provider
     * @param bool $isUniversity Whether the email is from a university
     * @param bool $isSpam Whether the email is from a spam domain
     * @param int $risk Risk score (0-100)
     * @param DnsInfo $dns DNS information
     * @param QuotaInfo|null $quota Quota information from response headers
     */
    public function __construct(
        private readonly string $email,
        private readonly string $user,
        private readonly string $domain,
        private readonly bool $isValid,
        private readonly bool $isDisposable,
        private readonly bool $isPublicProvider,
        private readonly bool $isUniversity,
        private readonly bool $isSpam,
        private readonly int $risk,
        private readonly DnsInfo $dns,
        private readonly ?QuotaInfo $quota = null
    ) {
    }

    /**
     * Get the complete email address.
     *
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Get the username/local part.
     *
     * @return string
     */
    public function getUser(): string
    {
        return $this->user;
    }

    /**
     * Get the domain part.
     *
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * Check if the email is valid.
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->isValid;
    }

    /**
     * Check if the email is from a disposable provider.
     *
     * @return bool
     */
    public function isDisposable(): bool
    {
        return $this->isDisposable;
    }

    /**
     * Check if the email is from a public provider.
     *
     * @return bool
     */
    public function isPublicProvider(): bool
    {
        return $this->isPublicProvider;
    }

    /**
     * Check if the email is from a university.
     *
     * @return bool
     */
    public function isUniversity(): bool
    {
        return $this->isUniversity;
    }

    /**
     * Check if the email is from a spam domain.
     *
     * @return bool
     */
    public function isSpam(): bool
    {
        return $this->isSpam;
    }

    /**
     * Get the risk score (0-100).
     *
     * @return int
     */
    public function getRisk(): int
    {
        return $this->risk;
    }

    /**
     * Get the DNS information.
     *
     * @return DnsInfo
     */
    public function getDns(): DnsInfo
    {
        return $this->dns;
    }

    /**
     * Get the quota information.
     *
     * @return QuotaInfo|null
     */
    public function getQuota(): ?QuotaInfo
    {
        return $this->quota;
    }

    /**
     * Create an EmailVerificationResult from an array and headers.
     *
     * @param array<string, mixed> $data
     * @param array<string, string> $headers
     * @return self
     */
    public static function fromArray(array $data, array $headers = []): self
    {
        $quota = !empty($headers) ? QuotaInfo::fromHeaders($headers) : null;

        return new self(
            $data['email'] ?? '',
            $data['user'] ?? '',
            $data['domain'] ?? '',
            $data['is_valid'] ?? false,
            $data['is_disposable'] ?? false,
            $data['is_public_provider'] ?? false,
            $data['is_university'] ?? false,
            $data['is_spam'] ?? false,
            $data['risk'] ?? 0,
            DnsInfo::fromArray($data['dns'] ?? []),
            $quota
        );
    }

    /**
     * Convert the result to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $result = [
            'email' => $this->email,
            'user' => $this->user,
            'domain' => $this->domain,
            'is_valid' => $this->isValid,
            'is_disposable' => $this->isDisposable,
            'is_public_provider' => $this->isPublicProvider,
            'is_university' => $this->isUniversity,
            'is_spam' => $this->isSpam,
            'risk' => $this->risk,
            'dns' => $this->dns->toArray(),
        ];

        if ($this->quota !== null) {
            $result['quota'] = $this->quota->toArray();
        }

        return $result;
    }
}
