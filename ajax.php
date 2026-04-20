<?php
require_once __DIR__ . '/init.php';
use WHMCS\Database\Capsule;

/**
 * Ação de Pagamento (UpdateInvoice + Redirect)
 */
$action = $_REQUEST['action'] ?? '';

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
 * Lógica de Busca de Faturas (Original)
 */
$email = $_POST['email'] ?? '';

if (empty($email)) {
    die('<div class="alert alert-warning">Por favor, informe seu e-mail.</div>');
}

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
    
    // Link de Pagamento Rápido (Aciona o UpdateInvoice no ajax.php)
    $payPixLink = "ajax.php?action=pay&invoiceid={$f->id}&gateway=bancointer_pix";
  
  
  	// Link de Pagamento Rápido (Aciona o UpdateInvoice no ajax.php)
    $payCreditCardLink = "ajax.php?action=pay&invoiceid={$f->id}&gateway=gofasiugucartao";

    echo "
    <div class='list-group-item list-group-item-action flex-column align-items-start py-3'>
        <div class='d-flex w-100 justify-content-between align-items-center'>
            <h6 class='mb-1 text-primary font-weight-bold'>Fatura #{$f->id}</h6>
            <span class='badge badge-warning p-2'>Pendente</span>
        </div>
        <p class='mb-2 text-muted small'>Vencimento: {$duedate} | Total: <strong>R$ {$total}</strong></p>
        <div class='d-flex gap-2 mt-2'>
            <a href='{$viewLink}' target='_blank' class='btn btn-lg btn-outline-secondary mr-2'>
                <i class='fas fa-eye'></i> Ver Fatura
            </a>
            <a href='{$payPixLink}' target='_blank' class='btn btn-lg btn-primary px-4 font-weight-bold'>
                <i class='fas fa-qrcode'></i> Pagar com PIX
            </a>
            <a href='{$payCreditCardLink}' target='_blank' class='btn btn-lg  px-4 '>
                <i class='fas fa-credit-card'></i> Pagar com Cartão
            </a>
        </div>
    </div>";
}

echo '</div>';

