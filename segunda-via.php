<?php
define("CLIENTAREA", true);
require_once __DIR__ . '/init.php';

use WHMCS\Database\Capsule;

// Handle AJAX / Form actions
$action = $_REQUEST['action'] ?? '';
$email = $_POST['email'] ?? '';

/**
 * Ação de Pagamento (UpdateInvoice + Redirect)
 */
if ($action === 'pay') {
    $invoiceId = (int)$_GET['invoiceid'];
    $gateway = $_GET['gateway'] ?? 'pix';

    // 1. Buscar informações da fatura
    $invoice = Capsule::table('tblinvoices')->where('id', $invoiceId)->first();
    
    if (!$invoice) {
        die('Fatura não encontrada.');
    }

    // 2. Buscar um administrador para a API
    $adminUser = Capsule::table('tbladmins')->where('disabled', 0)->value('username');

    // 3. Atualizar o método de pagamento via localAPI
    localAPI('UpdateInvoice', array(
        'invoiceid' => $invoiceId,
        'paymentmethod' => $gateway
    ), $adminUser);

    // 4. Gerar token SSO para acesso sem login
    $results = localAPI('CreateSsoToken', array(
        'client_id' => $invoice->userid,
        'destination' => 'sso:custom_redirect',
        'sso_redirect_path' => 'viewinvoice.php?id=' . $invoiceId
    ), $adminUser);

    if ($results['result'] == 'success') {
        header("Location: " . $results['redirect_url']);
        exit;
    }

    header("Location: viewinvoice.php?id=" . $invoiceId);
    exit;
}

/**
 * Lógica de Busca de Faturas (via POST)
 */
if (!empty($email)) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die('<div class="alert alert-danger">E-mail inválido.</div>');
    }

    // Buscar o cliente pelo e-mail
    $cliente = Capsule::table('tblclients')
        ->where('email', $email)
        ->select('id', 'firstname', 'lastname')
        ->first();

    if (!$cliente) {
        die('<div class="alert alert-danger">Nenhuma conta encontrada com este e-mail.</div>');
    }

    // 2. Verificar se a 2ª Via está desativada para este cliente
    // Procuramos por um campo personalizado chamado "Desativar 2ª Via"
    $isDisabled = Capsule::table('tblcustomfieldsvalues')
        ->join('tblcustomfields', 'tblcustomfields.id', '=', 'tblcustomfieldsvalues.fieldid')
        ->where('tblcustomfields.type', 'client')
        ->where('tblcustomfields.fieldname', 'like', '%Desativar 2ª Via%')
        ->where('tblcustomfieldsvalues.relid', $cliente->id)
        ->value('value');

    if (strtolower($isDisabled) == 'yes' || strtolower($isDisabled) == 'on' || $isDisabled == '1') {
        die('<div class="alert alert-warning shadow-sm border-0 py-3 animate-fade-in text-center">
                <i class="fas fa-user-lock fa-2x mb-3 d-block text-warning"></i>
                <h6 class="font-weight-bold">Acesso Restrito</h6>
                <p class="mb-0 small">Identificamos que o acesso simplificado está desativado para sua conta.<br>Por favor, acesse a Área do Cliente com sua senha ou contate o suporte.</p>
             </div>');
    }

    /**
     * Função auxiliar para gerar link SSO
     */
    $getSsoUrl = function($invoiceId, $clientId) {
        $adminUser = Capsule::table('tbladmins')->where('disabled', 0)->value('username');
        if (!$adminUser) return "viewinvoice.php?id=" . $invoiceId;

        $results = localAPI('CreateSsoToken', array(
            'client_id' => $clientId,
            'destination' => 'sso:custom_redirect',
            'sso_redirect_path' => 'viewinvoice.php?id=' . $invoiceId
        ), $adminUser);

        return ($results['result'] == 'success') ? $results['redirect_url'] : "viewinvoice.php?id=" . $invoiceId;
    };

    // Buscar faturas do cliente
    $faturas = Capsule::table('tblinvoices')
        ->where('userid', $cliente->id)
        ->where('status', 'Unpaid')
        ->orderBy('id', 'desc')
        ->get();

    if ($faturas->isEmpty()) {
        echo '<div class="alert alert-info text-center">
                <i class="fas fa-check-circle fa-2x mb-3 d-block"></i>
                Parabéns, não encontramos faturas pendentes.
              </div>';
        exit;
    }

    echo '<h5 class="mb-3 mt-2">Olá, ' . $cliente->firstname . '. Encontramos as seguintes faturas:</h5>';
    echo '<div class="list-group shadow-sm">';

    foreach ($faturas as $f) {
        $duedate = date('d/m/Y', strtotime($f->duedate));
        $total = number_format($f->total, 2, ',', '.');
        
        // Link SSO (Apenas visualização)
        $viewLink = $getSsoUrl($f->id, $cliente->id);
        
        // Links de Pagamento
        $payPixLink = "segunda-via.php?action=pay&invoiceid={$f->id}&gateway=bancointer_pix";
        $payCreditCardLink = "segunda-via.php?action=pay&invoiceid={$f->id}&gateway=gofasiugucartao";

        echo "
        <div class='invoice-card animate-fade-in'>
            <div class='invoice-info'>
                <h4>Fatura #{$f->id}</h4>
                <p>Vencimento: <strong>{$duedate}</strong> • Total: <strong class='text-primary'>R$ {$total}</strong></p>
            </div>
            <div class='invoice-actions'>
                <a href='{$viewLink}' target='_blank' class='btn-action btn-secondary btn-view'>
                    <i class='fas fa-eye'></i> Ver
                </a>
                <a href='{$payPixLink}' target='_blank' class='btn-action btn-primary-new btn-pay'>
                    <i class='fas fa-qrcode'></i> PIX
                </a>
                <a href='{$payCreditCardLink}' target='_blank' class='btn-action btn-primary-new btn-pay'>
                    <i class='fas fa-credit-card'></i> Cartão
                </a>
            </div>
        </div>";
    }

    echo '</div>';
    exit;
}

$ca = new WHMCS\ClientArea();
$ca->setPageTitle("2ª Via de Pagamento");
$ca->addToBreadCrumb('index.php', 'Portal');
$ca->addToBreadCrumb('segunda-via.php', '2ª Via de Pagamento');
$ca->initPage();

// O WHMCS busca o template relativo à pasta do tema ativo.
// Usamos o caminho partindo da raiz para garantir compatibilidade.
$ca->setTemplate('/modules/addons/invoice_recovery/templates/clientarea.tpl');

$ca->output();
