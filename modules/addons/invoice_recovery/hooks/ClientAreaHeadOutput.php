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
            /* Ocultar elementos de navegação lateral, topo e rodapé de forma específica */
            .sidebar, .breadcrumb, .navbar, .main-navigation, 
            .navigation, .user-controls, .account-links,
            .header-nav, .left-sidebar, .right-sidebar,
            #main-menu, #primary-nav, #secondary-nav, .topbar, .top-nav,
            .app-nav, .sticky-navigation,
            .footer-area, .site-header, .site-footer,
            #header, #footer, #sidebar, header, footer, aside, nav { 
                display: none !important; 
                visibility: hidden !important; 
                opacity: 0 !important; 
                height: 0 !important; 
                overflow: hidden !important;
                pointer-events: none !important; 
            }
            
            body { padding-top: 0 !important; margin-top: 0 !important; background: #fff !important; }
            
            /* Garantir que o conteúdo da fatura e os botões de pagamento SEJAM visíveis */
            .main-content, #main-body, .page-main, .invoice-container, .view-invoice, .payment-btn-container { 
                margin: 0 auto !important; 
                padding: 20px !important; 
                width: 100% !important; 
                max-width: 1000px !important; 
                display: block !important;
                visibility: visible !important;
                opacity: 1 !important;
                height: auto !important;
                pointer-events: auto !important;
            }
            
            /* Impedir que seletores internos da fatura que usem nomes genéricos sejam escondidos */
            .invoice-container div, .invoice-container section, .invoice-container header, .invoice-container footer {
                display: block !important;
                visibility: visible !important;
                opacity: 1 !important;
                height: auto !important;
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
