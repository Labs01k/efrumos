<?php

namespace App\Exceptions\Integration;

use RuntimeException;

/**
 * Epic 0 / 0.2 — thrown when 1С reports less stock than the order needs.
 * Deliberately its own exception type (not a generic gateway failure) so the
 * order lands in a distinct "not enough stock" state rather than the retry
 * queue — retrying won't manufacture inventory. No negative stock is ever
 * written; this is the predictable-handling path the acceptance criteria asks for.
 */
class InsufficientStockException extends RuntimeException
{
    public function __construct(public readonly string $sku, public readonly int $requested, public readonly int $available)
    {
        parent::__construct("SKU {$sku}: requested {$requested}, only {$available} available in 1С.");
    }
}
