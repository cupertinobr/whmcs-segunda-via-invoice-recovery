<?php

namespace branix\WhmcsInvoiceRecovery;

use WHMCS\Database\Capsule;

class InvoiceSearchService
{
    /**
     * Tries to locate a client by email or CPF/CNPJ (Brazilian ID numbers).
     * Returns the client object or null if not found.
     */
    public static function findClient(string $inputRaw, int $cpfFieldId): ?object
    {
        $inputRaw = trim($inputRaw);

        if (filter_var($inputRaw, FILTER_VALIDATE_EMAIL)) {
            return Capsule::table('tblclients')
                ->where('email', strtolower($inputRaw))
                ->where('status', '!=', 'Closed')
                ->select('id', 'firstname', 'lastname', 'email', 'currency')
                ->first();
        }

        // Try as CPF or CNPJ
        $docLimpo = DocumentValidator::clean($inputRaw);

        if (!DocumentValidator::validateCpf($docLimpo) && !DocumentValidator::validateCnpj($docLimpo)) {
            return null;
        }

        if (!$cpfFieldId) {
            return null;
        }

        // Search with formatting normalization on the database side
        return Capsule::table('tblcustomfieldsvalues')
            ->join('tblclients', 'tblclients.id', '=', 'tblcustomfieldsvalues.relid')
            ->where('tblcustomfieldsvalues.fieldid', $cpfFieldId)
            ->where('tblclients.status', '!=', 'Closed')
            ->whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(tblcustomfieldsvalues.value, '.', ''), '-', ''), '/', ''), ' ', '') = ?", [$docLimpo])
            ->select('tblclients.id', 'tblclients.firstname', 'tblclients.lastname', 'tblclients.email', 'tblclients.currency')
            ->first();
    }

    /**
     * Checks if simplified invoice recovery is disabled for the client.
     */
    public static function isClientBlocked(int $clientId, int $blockFieldId): bool
    {
        $statusBlocked = null;

        if ($blockFieldId > 0) {
            $statusBlocked = Capsule::table('tblcustomfieldsvalues')
                ->where('fieldid', $blockFieldId)
                ->where('relid', $clientId)
                ->value('value');
        } else {
            $statusBlocked = Capsule::table('tblcustomfieldsvalues')
                ->join('tblcustomfields', 'tblcustomfields.id', '=', 'tblcustomfieldsvalues.fieldid')
                ->where('tblcustomfields.type', 'client')
                ->where('tblcustomfields.fieldname', 'like', '%Desativar 2ª Via%')
                ->where('tblcustomfieldsvalues.relid', $clientId)
                ->value('value');
        }

        return (strtolower($statusBlocked) === 'yes' || strtolower($statusBlocked) === 'on' || $statusBlocked === '1');
    }

    /**
     * Returns a list of pending invoices for the client.
     */
    public static function getPendingInvoices(int $clientId): array
    {
        return Capsule::table('tblinvoices')
            ->where('userid', $clientId)
            ->where('status', 'Unpaid')
            ->orderBy('id', 'desc')
            ->get()
            ->all();
    }

    /**
     * Formats the invoice amount using the client's currency configuration in WHMCS.
     */
    public static function formatCurrency(float $amount, int $currencyId, array $lang): string
    {
        try {
            $currency = Capsule::table('tblcurrencies')->where('id', $currencyId)->first();
            if ($currency) {
                $prefix = $currency->prefix;
                $suffix = $currency->suffix;
                $decPoint = $currency->decimalseparator ?? $lang['decimal_separator'] ?? ',';
                $thousandsSep = $currency->thousandsseparator ?? $lang['thousands_separator'] ?? '.';

                return $prefix . number_format($amount, 2, $decPoint, $thousandsSep) . $suffix;
            }
        } catch (\Exception $e) {
            // Silently ignore and fallback
        }

        // Fallback using module language variables
        $decPoint = $lang['decimal_separator'] ?? ',';
        $thousandsSep = $lang['thousands_separator'] ?? '.';
        $prefix = $lang['currency_prefix'] ?? 'R$ ';
        $suffix = $lang['currency_suffix'] ?? '';

        return $prefix . number_format($amount, 2, $decPoint, $thousandsSep) . $suffix;
    }
}
