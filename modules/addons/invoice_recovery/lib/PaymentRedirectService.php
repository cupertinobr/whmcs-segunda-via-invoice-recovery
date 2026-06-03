<?php

namespace branix\WhmcsInvoiceRecovery;

use WHMCS\Database\Capsule;

class PaymentRedirectService
{
    /**
     * Updates the payment gateway of the invoice and generates the target URL (with or without SSO).
     * Returns the redirect URL.
     */
    public static function processPaymentRedirect(
        int $invoiceId,
        string $gateway,
        array $addonConfig,
        array $session,
        bool $disableSso,
        string $adminUser
    ): string {
        $invoice = Capsule::table('tblinvoices')
            ->where('id', $invoiceId)
            ->where('userid', $session['client_id'])
            ->where('status', 'Unpaid')
            ->first();

        if (!$invoice) {
            throw new \Exception('invoice_not_found');
        }

        // Update the invoice gateway via localAPI
        localAPI('UpdateInvoice', [
            'invoiceid' => $invoiceId,
            'paymentmethod' => $gateway
        ], $adminUser);

        // Log the activity
        LoggerService::log(
            $_SERVER['REMOTE_ADDR'] ?? '',
            (int)$session['client_id'],
            'payment_initiated',
            "Iniciado pagamento da Fatura #{$invoiceId} via gateway '{$gateway}'",
            (float)$invoice->total
        );

        $destinationUrl = 'viewinvoice.php?id=' . $invoiceId . '&restricted=1';

        if (!$disableSso && !empty($adminUser)) {
            $results = localAPI('CreateSsoToken', [
                'client_id' => $session['client_id'],
                'destination' => 'sso:custom_redirect',
                'sso_redirect_path' => $destinationUrl
            ], $adminUser);

            if (isset($results['result']) && $results['result'] === 'success') {
                return $results['redirect_url'] . '&restricted=1';
            }
        }

        return $destinationUrl;
    }

    /**
     * Generates the SSO URL for viewing the invoice only ("View" button).
     */
    public static function processViewRedirect(
        int $invoiceId,
        array $session,
        bool $disableSso,
        string $adminUser
    ): string {
        $invoice = Capsule::table('tblinvoices')
            ->where('id', $invoiceId)
            ->where('userid', $session['client_id'])
            ->where('status', 'Unpaid')
            ->first();

        if (!$invoice) {
            throw new \Exception('invoice_not_found');
        }

        // Log the activity
        LoggerService::log(
            $_SERVER['REMOTE_ADDR'] ?? '',
            (int)$session['client_id'],
            'invoice_viewed',
            "Visualização da Fatura #{$invoiceId}"
        );

        $destinationUrl = 'viewinvoice.php?id=' . $invoiceId . '&restricted=1';

        if (!$disableSso && !empty($adminUser)) {
            $results = localAPI('CreateSsoToken', [
                'client_id' => $session['client_id'],
                'destination' => 'sso:custom_redirect',
                'sso_redirect_path' => $destinationUrl
            ], $adminUser);

            if (isset($results['result']) && $results['result'] === 'success') {
                return $results['redirect_url'] . '&restricted=1';
            }
        }

        return $destinationUrl;
    }
}
