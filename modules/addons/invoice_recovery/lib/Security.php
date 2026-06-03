<?php

namespace branix\WhmcsInvoiceRecovery;

class Security
{
    public const SESSION_KEY = 'invoice_recovery_auth';
    public const SESSION_TTL = 3600;
    public const TOKEN_SCOPE = 'invoice_recovery';

    /** Allowed pages during the restricted SSO session mode. */
    public const RESTRICTED_ALLOWED_PAGES = [
        'viewinvoice.php',
        'logout.php',
        'singlesignon.php',
        'dologin.php',
        'invoicepayment.php',
        'payment.php',
        'forward.php',
        'checkout.php',
        'creditcard.php',
    ];

    public static function generateCsrfToken(): string
    {
        return generate_token(self::TOKEN_SCOPE);
    }

    public static function validateCsrfToken(?string $token): bool
    {
        return check_token(self::TOKEN_SCOPE, $token ?? '');
    }

    public static function establishSession(int $clientId, array $invoiceIds): void
    {
        $_SESSION[self::SESSION_KEY] = [
            'client_id' => $clientId,
            'invoice_ids' => array_map('intval', $invoiceIds),
            'expires_at' => time() + self::SESSION_TTL,
        ];
    }

    public static function clearSession(): void
    {
        unset($_SESSION[self::SESSION_KEY]);
    }

    public static function getSession(): ?array
    {
        $session = $_SESSION[self::SESSION_KEY] ?? null;

        if (!is_array($session)) {
            return null;
        }

        if (($session['expires_at'] ?? 0) < time()) {
            self::clearSession();
            return null;
        }

        return $session;
    }

    public static function isInvoiceAuthorized(int $invoiceId): bool
    {
        $session = self::getSession();

        if (!$session) {
            return false;
        }

        return in_array($invoiceId, $session['invoice_ids'] ?? [], true);
    }

    /**
     * @return string[]
     */
    public static function getAllowedGateways(array $addonConfig): array
    {
        $gateways = [];

        foreach (['pix_gateway_id', 'boleto_gateway_id', 'cc_gateway_id'] as $key) {
            $value = trim($addonConfig[$key] ?? '');
            if ($value !== '') {
                $gateways[] = $value;
            }
        }

        return array_values(array_unique($gateways));
    }

    public static function isGatewayAllowed(string $gateway, array $addonConfig): bool
    {
        $gateway = trim($gateway);

        if ($gateway === '') {
            return false;
        }

        return in_array($gateway, self::getAllowedGateways($addonConfig), true);
    }

    public static function sanitizeGateway(string $gateway): string
    {
        return preg_replace('/[^a-zA-Z0-9_]/', '', $gateway) ?? '';
    }

    public static function isRestrictedPageAllowed(string $script): bool
    {
        return in_array($script, self::RESTRICTED_ALLOWED_PAGES, true);
    }

    public static function restrictedAllowedPagesJson(): string
    {
        return json_encode(self::RESTRICTED_ALLOWED_PAGES);
    }
}
