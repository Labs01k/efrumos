<?php

namespace App\Exceptions\Integration;

use RuntimeException;

/**
 * Epic 0 / 0.4 — a 1С/Bitrix24 call failed (network, timeout, non-2xx,
 * malformed response). Thrown by gateway implementations so
 * OrderIntegrationService and the retry job have one type to catch,
 * regardless of which system or which HTTP client raised it underneath.
 */
class IntegrationGatewayException extends RuntimeException
{
}
