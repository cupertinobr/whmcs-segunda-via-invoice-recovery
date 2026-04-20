<?php
use WHMCS\Database\Capsule;

add_hook('ClientAreaInit', 1, function() {
    if(isset($_GET['gateway'], $_GET['id'])) {
        Capsule::table('tblinvoices')
            ->where('id', (int)$_GET['id'])
            ->update(['paymentmethod' => $_GET['gateway']]);
    }
});

/**
 * Restrição de Acesso 2ª Via
 * Impede que o cliente navegue por outras áreas se logado via link de recuperação
 */
add_hook('ClientAreaPage', 1, function($vars) {
    if (!empty($_SESSION['invoice_recovery_only'])) {
        $template = $vars['templatefile'] ?? '';
        
        // Páginas permitidas: visualização de fatura e logout
        $allowed = ['viewinvoice', 'logout'];
        
        if (!in_array($template, $allowed)) {
            // Se tentar acessar outra área, remove a flag e desloga
            unset($_SESSION['invoice_recovery_only']);
            header("Location: logout.php?returnurl=segunda-via.php");
            exit;
        }
    }
});
