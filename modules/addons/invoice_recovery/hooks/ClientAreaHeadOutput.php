<?php

use branix\WhmcsInvoiceRecovery\Security;

require_once dirname(__DIR__) . '/lib/Security.php';

add_hook('ClientAreaHeadOutput', 1, function ($vars) {
    $isRestricted = !empty($_SESSION['restricted_invoice_mode'])
        || (isset($_REQUEST['restricted']) && $_REQUEST['restricted'] == '1');

    if (!$isRestricted) {
        return '';
    }

    $allowedPagesJson = Security::restrictedAllowedPagesJson();

    return "
        <style id='restrictedModeStyle'>
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
                var allowedPages = {$allowedPagesJson};
                var currentPage = window.location.pathname.split('/').pop();
                var isPageAllowed = allowedPages.some(function(page) {
                    return currentPage.indexOf(page) !== -1;
                });

                if (!isPageAllowed && window.location.href.indexOf('restricted=1') === -1) {
                    window.location.href = 'logout.php?returnurl=segunda-via.php';
                }

                setInterval(function() {
                    if (!document.getElementById('restrictedModeStyle')) {
                        window.location.reload();
                    }
                }, 2000);
            })();
        </script>";
});
