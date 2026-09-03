<?php

namespace App\Services\Payment\Victoriabank;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * VictoriaBank card payments via the e-Gateway CGI protocol — RSA-2048/
 * SHA-256 signed form POST, redirect-based 3-D Secure, server callback as
 * the source of truth. Test terminal/merchant and the bank's public key
 * came from VictoriaBank's own onboarding email + integration guide.
 *
 * Flow: buildAuthorizationForm() (TRTYPE=0) -> customer redirected to bank
 * -> verifyCallbackSignature() on the server callback -> complete() (TRTYPE=21)
 * to capture funds. reverse() (TRTYPE=24) refunds a completed transaction.
 */
class VictoriaBankClient
{
    public function __construct(
        private readonly string $endpointUrl,
        private readonly string $terminalId,
        private readonly string $merchantId,
        private readonly string $merchantPrivateKeyPath,
        private readonly string $bankPublicKeyPath,
        private readonly string $currency,
        private readonly string $merchantName,
        private readonly string $merchantUrl,
        private readonly string $country,
        private readonly string $merchGmt,
    ) {
    }

    public static function fromConfig(): self
    {
        $c = config('services.victoriabank');

        return new self(
            endpointUrl: $c['endpoint_url'],
            terminalId: $c['terminal_id'],
            merchantId: $c['merchant_id'],
            merchantPrivateKeyPath: $c['merchant_private_key_path'],
            bankPublicKeyPath: $c['bank_public_key_path'],
            currency: $c['currency'],
            merchantName: $c['merchant_name'],
            merchantUrl: $c['merchant_url'],
            country: $c['country'],
            merchGmt: $c['merch_gmt'],
        );
    }

    /**
     * Build the ORDER, NONCE, TIMESTAMP, AMOUNT and P_SIGN fields for
     * TRTYPE=0/21/24, per "P_SIGN Algorithm" — MAC field order is
     * ORDER -> NONCE -> TIMESTAMP -> TRTYPE -> AMOUNT, each field encoded
     * as length(value).value (or "-" if empty), concatenated, RSA-SHA256
     * signed with our private key, hex-encoded uppercase.
     */
    public function psignGenerate(string $order, string $nonce, string $timestamp, string $trtype, string $amount): string
    {
        $mac = self::buildMac([$order, $nonce, $timestamp, $trtype, $amount]);

        $privateKey = openssl_pkey_get_private('file://' . $this->merchantPrivateKeyPath);
        if ($privateKey === false) {
            throw new \RuntimeException('Cannot load VictoriaBank merchant private key from ' . $this->merchantPrivateKeyPath);
        }

        openssl_sign($mac, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        return strtoupper(bin2hex($signature));
    }

    /**
     * Verify a callback's P_SIGN against the bank's public key.
     * MAC field order for callbacks is ACTION -> RC -> RRN -> ORDER -> AMOUNT
     * (note: different order than outgoing requests). Empty fields (e.g. RRN
     * on a declined transaction) encode as "-", not skipped, not "0" — a
     * very common integration bug per the bank's own docs.
     */
    public function verifyCallbackSignature(Request $request): bool
    {
        $pSign = trim((string) $request->input('P_SIGN', ''));
        if ($pSign === '' || !ctype_xdigit($pSign)) {
            return false;
        }

        $fields = array_map(
            fn (string $key) => trim((string) $request->input($key, '')),
            ['ACTION', 'RC', 'RRN', 'ORDER', 'AMOUNT'],
        );
        $mac = self::buildMac($fields);

        $publicKey = openssl_pkey_get_public('file://' . $this->bankPublicKeyPath);
        if ($publicKey === false) {
            return false;
        }

        $signatureBytes = hex2bin($pSign);
        if ($signatureBytes === false) {
            return false;
        }

        return openssl_verify($mac, $signatureBytes, $publicKey, OPENSSL_ALGO_SHA256) === 1;
    }

    /**
     * TRTYPE=0 — everything needed to render the auto-submitting HTML form
     * that redirects the customer to the bank's 3-D Secure page. The
     * controller owns rendering the actual <form>; this just produces the
     * signed field set and the endpoint to POST it to.
     */
    public function buildAuthorizationForm(string $orderId, float $amount, string $email, string $backrefUrl): array
    {
        $order = self::formatOrderId($orderId);
        $amountStr = self::formatAmount($amount);
        $nonce = self::generateNonce();
        $timestamp = self::generateTimestamp();
        $pSign = $this->psignGenerate($order, $nonce, $timestamp, '0', $amountStr);

        return [
            'endpoint' => $this->endpointUrl,
            'fields' => [
                'AMOUNT' => $amountStr,
                'CURRENCY' => $this->currency,
                'ORDER' => $order,
                'DESC' => 'Order #' . $order,
                'MERCH_NAME' => $this->merchantName,
                'MERCH_URL' => $this->merchantUrl,
                'MERCHANT' => $this->merchantId,
                'TERMINAL' => $this->terminalId,
                'EMAIL' => $email,
                'TRTYPE' => '0',
                'COUNTRY' => $this->country,
                'MERCH_GMT' => $this->merchGmt,
                'TIMESTAMP' => $timestamp,
                'NONCE' => $nonce,
                'BACKREF' => $backrefUrl,
                'LANG' => 'ro',
                'P_SIGN' => $pSign,
            ],
        ];
    }

    /**
     * TRTYPE=21 — capture funds after a successful callback. Must use the
     * exact same AMOUNT as the authorization, and the RRN/INT_REF received
     * in that callback. Bank responds with an HTML page containing hidden
     * <input> fields (not JSON, not form-encoded) — parsed with regex per
     * the bank's own reference implementation.
     */
    public function complete(string $orderId, float $amount, string $rrn, string $intRef): array
    {
        $order = self::formatOrderId($orderId);
        $amountStr = self::formatAmount($amount);
        $nonce = self::generateNonce();
        $timestamp = self::generateTimestamp();
        $pSign = $this->psignGenerate($order, $nonce, $timestamp, '21', $amountStr);

        $response = Http::asForm()->timeout(30)->post($this->endpointUrl, [
            'ORDER' => $order,
            'AMOUNT' => $amountStr,
            'CURRENCY' => $this->currency,
            'TERMINAL' => $this->terminalId,
            'TRTYPE' => '21',
            'TIMESTAMP' => $timestamp,
            'NONCE' => $nonce,
            'RRN' => $rrn,
            'INT_REF' => $intRef,
            'P_SIGN' => $pSign,
        ]);

        return self::parseHtmlHiddenInputs($response->body());
    }

    /**
     * TRTYPE=24 — refund a completed transaction, full or partial. Only one
     * reversal is allowed per transaction; a second attempt returns RC=95
     * (already reversed — not necessarily a failure, check records first).
     * Unlike TRTYPE=21, the bank responds with a URL-encoded string, not HTML.
     */
    public function reverse(string $orderId, float $amount, string $rrn, string $intRef): array
    {
        $order = self::formatOrderId($orderId);
        $amountStr = self::formatAmount($amount);
        $nonce = self::generateNonce();
        $timestamp = self::generateTimestamp();
        $pSign = $this->psignGenerate($order, $nonce, $timestamp, '24', $amountStr);

        $response = Http::asForm()->timeout(30)->post($this->endpointUrl, [
            'ORDER' => $order,
            'AMOUNT' => $amountStr,
            'CURRENCY' => $this->currency,
            'TERMINAL' => $this->terminalId,
            'TRTYPE' => '24',
            'TIMESTAMP' => $timestamp,
            'NONCE' => $nonce,
            'RRN' => $rrn,
            'INT_REF' => $intRef,
            'P_SIGN' => $pSign,
        ]);

        parse_str($response->body(), $result);

        return $result;
    }

    /**
     * TRTYPE=90 — polling fallback for when the callback never arrives (e.g.
     * customer closed the browser mid-3DS). No P_SIGN/TIMESTAMP/NONCE needed —
     * the simplest request type per the bank's docs. Response is an HTML page
     * with hidden <input> fields, same shape as TRTYPE=21.
     */
    public function checkStatus(string $orderId, string $tranTrtype = '0'): array
    {
        $order = self::formatOrderId($orderId);

        $response = Http::asForm()->timeout(30)->post($this->endpointUrl, [
            'TERMINAL' => $this->terminalId,
            'TRTYPE' => '90',
            'TRAN_TRTYPE' => $tranTrtype,
            'ORDER' => $order,
        ]);

        return self::parseHtmlHiddenInputs($response->body());
    }

    /**
     * MAC string: for each field, length(value) + value; empty or "-" -> "-".
     */
    public static function buildMac(array $values): string
    {
        $mac = '';
        foreach ($values as $v) {
            $v = (string) $v;
            $mac .= ($v === '' || $v === '-') ? '-' : strlen($v) . $v;
        }

        return $mac;
    }

    public static function generateNonce(): string
    {
        return strtoupper(bin2hex(random_bytes(20))); // 40 hex chars, the documented minimum
    }

    public static function generateTimestamp(): string
    {
        return gmdate('YmdHis');
    }

    public static function formatOrderId(string $id): string
    {
        return str_pad($id, 6, '0', STR_PAD_LEFT);
    }

    public static function formatAmount(float $amount): string
    {
        return number_format($amount, 2, '.', '');
    }

    private static function parseHtmlHiddenInputs(string $html): array
    {
        preg_match_all('/name=["\'](\w+)["\'][^>]+value=["\']([^"\']*)/i', $html, $matches, PREG_SET_ORDER);

        $result = [];
        foreach ($matches as $m) {
            $result[$m[1]] = $m[2];
        }

        return $result;
    }
}
