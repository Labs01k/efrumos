<?php

namespace App\Services\AccountLink;

use RuntimeException;

/**
 * `$reason` — машиночитаемый код для приложения (решить, какой текст/экран
 * показать), `$message` — для логов. Аналог App\Http\Controllers\AccountLinkConflictException
 * в eFrumos-backend, та же идея на стороне сайта.
 */
class AccountLinkRedeemException extends RuntimeException
{
    public const INVALID = 'invalid';
    public const USED = 'used';
    public const EXPIRED = 'expired';
    public const PKCE_MISMATCH = 'pkce_mismatch';
    public const LOYALTY_CLIENT_TAKEN = 'loyalty_client_taken';

    public function __construct(string $message, private readonly string $reason)
    {
        parent::__construct($message);
    }

    public function reason(): string
    {
        return $this->reason;
    }
}
