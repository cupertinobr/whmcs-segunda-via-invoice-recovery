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

/**
 * Forçar ocultação dos menus via CSS (Medida Visual) + Backup JS
 */
add_hook('ClientAreaHeadOutput', 1, function($vars) {
    if (!empty($_SESSION['restricted_invoice_mode'])) {
        return "
        <style id='restrictedModeStyle'>
            /* Ocultar elementos de navegação e conta */
            header, footer, .sidebar, .breadcrumb, .navbar, .main-navigation, 
            aside, nav, .navigation, .user-controls, .account-links,
            .header-nav, .left-sidebar, .right-sidebar { 
                display: none !important; 
                visibility: hidden !important; 
                opacity: 0 !important; 
                height: 0 !important; 
                pointer-events: none !important; 
            }
            body { padding-top: 0 !important; margin-top: 0 !important; }
            .main-content { margin-top: 0 !important; padding-top: 20px !important; width: 100% !important; max-width: 100% !important; left: 0 !important; }
            .container { width: 100% !important; max-width: 1200px; }
        </style>
        <script>
            // Medida extra de proteção via JS
            if (window.location.href.indexOf('viewinvoice.php') === -1 && window.location.href.indexOf('logout') === -1 && window.location.href.indexOf('singlesignon') === -1) {
                window.location.href = 'logout.php?returnurl=segunda-via.php';
            }
        </script>";
    }
});
