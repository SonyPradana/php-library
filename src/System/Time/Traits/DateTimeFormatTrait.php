<?php

declare(strict_types=1);

namespace System\Time\Traits;

trait DateTimeFormatTrait
{
    /**
     * Format: Y-m-d\\TH:i:sP.
     */
    public function formatATOM(): string
    {
        return $this->format(\DateTimeInterface::ATOM);
    }

    /**
     * Format: l, d-M-Y H:i:s T.
     */
    public function formatCOOKIE(): string
    {
        return $this->format(\DateTimeInterface::COOKIE);
    }

    /**
     * Format: D, d M y H:i:s O.
     */
    public function formatRFC822(): string
    {
        return $this->format(\DateTimeInterface::RFC822);
    }

    /**
     * Format: l, d-M-y H:i:s T.
     */
    public function formatRFC850(): string
    {
        return $this->format(\DateTimeInterface::RFC822);
    }

    /**
     * Format: D, d M y H:i:s O.
     */
    public function formatRFC1036(): string
    {
        return $this->format(\DateTimeInterface::RFC822);
    }

    /**
     * Format: D, d M Y H:i:s O.
     */
    public function formatRFC1123(): string
    {
        return $this->format(\DateTimeInterface::RFC1123);
    }

    /**
     * Format: D, d M Y H:i:s \\G\\M\\T.
     */
    public function formatRFC7231(): string
    {
        return $this->format(\DateTimeInterface::RFC7231);
    }

    /**
     * Format: D, d M Y H:i:s O.
     */
    public function formatRFC2822(): string
    {
        return $this->format(\DateTimeInterface::RFC2822);
    }

    /**
     * Format: Y-m-d\\TH:i:sP or Y-m-d\\TH:i:s.vP (expanded).
     */
    public function formatRFC3339(bool $expanded = false): string
    {
        return $this->format($expanded ? \DateTimeInterface::RFC3339_EXTENDED : \DateTimeInterface::RFC3339);
    }

    /**
     * Format: D, d M Y H:i:s O.
     */
    public function formatRSS(): string
    {
        return $this->format(\DateTimeInterface::RSS);
    }

    /**
     * Format: Y-m-d\\TH:i:sP.
     */
    public function formatW3C(): string
    {
        return $this->format(\DateTimeInterface::W3C);
    }
}
