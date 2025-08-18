<?php

declare(strict_types=1);

namespace System\RateLimitter;

final class RateLimit
{
    public function __construct(
        private string $identifier,
        private int $limit,
        private int $consumed,
        private int $remaining,
        private bool $isBlocked,
        private ?\DateTime $retryAfter = null,
        private ?\DateTime $expiresAt = null,
    ) {
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getConsumed(): int
    {
        return $this->consumed;
    }

    public function getRemaining(): int
    {
        return $this->remaining;
    }

    public function isBlocked(): bool
    {
        return $this->isBlocked;
    }

    public function getRetryAfter(): ?\DateTime
    {
        return $this->retryAfter;
    }

    public function getExpiresAt(): ?\DateTime
    {
        return $this->expiresAt;
    }
}
