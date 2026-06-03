<?php

use branix\WhmcsInvoiceRecovery\Security;

require_once dirname(__DIR__) . '/lib/Security.php';

add_hook('ClientAreaInit', 1, function () {
    if (isset($_REQUEST['restricted']) || strpos($_SERVER['QUERY_STRING'] ?? '', 'restricted=1') !== false) {
        $_SESSION['restricted_invoice_mode'] = true;
    }

    if (!empty($_SESSION['restricted_invoice_mode'])) {
        $script = basename($_SERVER['SCRIPT_NAME'] ?? '');

        if (!Security::isRestrictedPageAllowed($script)) {
            if (function_exists('logActivity')) {
                logActivity("Invoice Recovery: Restricted mode blocked access to script '{$script}' and triggered logout.");
            }
            unset($_SESSION['restricted_invoice_mode']);
            header('Location: logout.php?returnurl=segunda-via.php');
            exit;
        }
    }
});
