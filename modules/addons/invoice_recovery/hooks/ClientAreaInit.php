<?php
use WHMCS\Database\Capsule;

add_hook('ClientAreaInit', 1, function() {
    // 1. Capturar flag do SSO ou URL
    if (isset($_GET['restricted']) || strpos($_SERVER['QUERY_STRING'] ?? '', 'restricted=1') !== false) {
        $_SESSION['restricted_invoice_mode'] = true;
    }

    // 2. Monitoramento de Segurança
    if (!empty($_SESSION['restricted_invoice_mode'])) {
        $script = basename($_SERVER['SCRIPT_NAME']);
        $allowed = ['viewinvoice.php', 'logout.php', 'singlesignon.php', 'dologin.php'];
        
        if (!in_array($script, $allowed)) {
            // Se tentar navegar fora da fatura, desloga imediatamente
            unset($_SESSION['restricted_invoice_mode']);
            header("Location: logout.php?returnurl=segunda-via.php");
            exit;
        }
    }

    // 3. Processar atualização de gateway (lógica original)
    if(isset($_GET['gateway'], $_GET['id'])) {
        Capsule::table('tblinvoices')
            ->where('id', (int)$_GET['id'])
            ->update(['paymentmethod' => $_GET['gateway']]);
    }
});

