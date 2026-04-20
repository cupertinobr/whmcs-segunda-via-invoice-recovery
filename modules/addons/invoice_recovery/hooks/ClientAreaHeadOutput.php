<?php
use WHMCS\Database\Capsule;

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
