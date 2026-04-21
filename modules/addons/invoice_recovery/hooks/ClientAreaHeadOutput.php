<?php
use WHMCS\Database\Capsule;

/**
 * Forçar ocultação dos menus via CSS (Medida Visual) + Backup JS
 */
add_hook('ClientAreaHeadOutput', 1, function($vars) {
    // Verificar se está no modo restrito (pela sessão ou pelo parâmetro imediato na URL)
    $isRestricted = !empty($_SESSION['restricted_invoice_mode']) || (isset($_REQUEST['restricted']) && $_REQUEST['restricted'] == '1');

    if ($isRestricted) {
        return "
        <style id='restrictedModeStyle'>
            /* Ocultar elementos de navegação, sidebars, headers e footers genericamente */
            header, footer, aside, nav, 
            .sidebar, .breadcrumb, .navbar, .main-navigation, 
            .navigation, .user-controls, .account-links,
            .header-nav, .left-sidebar, .right-sidebar,
            #main-menu, #primary-nav, #secondary-nav,
            .topbar, .footer-area, .site-header, .site-footer,
            [class*='header'], [class*='footer'], [class*='sidebar'],
            [id*='header'], [id*='footer'], [id*='sidebar'],
            [id*='menu'], [id*='nav'] { 
                display: none !important; 
                visibility: hidden !important; 
                opacity: 0 !important; 
                height: 0 !important; 
                overflow: hidden !important;
                pointer-events: none !important; 
            }
            body { padding-top: 0 !important; margin-top: 0 !important; }
            /* Garantir que o conteúdo principal ocupe a tela toda */
            .main-content, #main-body, .page-main { 
                margin: 0 !important; 
                padding: 20px !important; 
                width: 100% !important; 
                max-width: 100% !important; 
                left: 0 !important; 
                display: block !important;
                visibility: visible !important;
                opacity: 1 !important;
            }
            .container { width: 100% !important; max-width: 1200px !important; }
        </style>
        <script>
            (function() {
                // Se não estiver em uma página permitida, força o logout para segurança
                var allowedPages = ['viewinvoice.php', 'logout.php', 'singlesignon.php', 'dologin.php', 'payment.php', 'forward.php'];
                var currentPage = window.location.pathname.split('/').pop();
                var isPageAllowed = false;

                for (var i = 0; i < allowedPages.length; i++) {
                    if (currentPage.indexOf(allowedPages[i]) !== -1) {
                        isPageAllowed = true;
                        break;
                    }
                }

                if (!isPageAllowed && window.location.href.indexOf('restricted=1') === -1) {
                    window.location.href = 'logout.php?returnurl=segunda-via.php';
                }

                // Proteção contra remoção do estilo via console
                setInterval(function() {
                    if (!document.getElementById('restrictedModeStyle')) {
                         window.location.reload();
                    }
                }, 2000);
            })();
        </script>";
    }
});
